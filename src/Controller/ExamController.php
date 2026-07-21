<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Entity\Question;
use App\Entity\StudySchedule;
use App\Form\ExamType;
use App\Repository\ExamMaterialRepository;
use App\Repository\ExamRepository;
use App\Repository\QuestionRepository;
use App\Repository\StudyScheduleRepository;
use App\Service\ScheduleGenerator;
use App\Service\XpService; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExamController extends AbstractController
{
    #[Route('/exams', name: 'app_exams')]
    public function index(ExamRepository $examRepository, ExamMaterialRepository $examMaterialRepository): Response
    {
        $user = $this->getUser();
        
        $exams = $examRepository->findByUserOrderedByDate($user);
        $materials = $examMaterialRepository->findByUserOrderedByUploadDate($user);
        
        return $this->render('exams/index.html.twig', [
            'exams' => $exams,
            'materials' => $materials,
        ]);
    }

    #[Route('/exam/new', name: 'app_exam_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exam = new Exam();
        $exam->setUser($this->getUser());
        
        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exam);
            $entityManager->flush();
            
            $this->addFlash('success', 'Экзамен успешно добавлен!');
            return $this->redirectToRoute('app_exams');
        }
        
        return $this->render('exam/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/exam/{id}/generate-schedule', name: 'app_exam_generate_schedule', methods: ['POST'])]
    public function generateSchedule(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ScheduleGenerator $scheduleGenerator,
        ExamRepository $examRepository
    ): Response {
        $exam = $examRepository->findForUser($id, $this->getUser());
        
        if (!$exam) {
            throw $this->createNotFoundException('Экзамен не найден');
        }
        
        if (!$this->isCsrfTokenValid('generate_schedule_' . $exam->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }
        
        $result = $scheduleGenerator->generate($exam);
        
        if (isset($result['error'])) {
            $this->addFlash('error', $result['error']);
        } else {
            $this->addFlash('success', 
                "✅ Расписание успешно создано! " .
                "Всего вопросов: {$result['totalQuestions']}, " .
                "Дней подготовки: {$result['availableDaysCount']}, " .
                "Вопросов в день: {$result['questionsPerDay']}"
            );
        }
        
        return $this->redirectToRoute('app_exams');
    }

    #[Route('/exam/{id}/schedule', name: 'app_exam_schedule')]
    public function schedule(int $id, ExamRepository $examRepository, StudyScheduleRepository $scheduleRepository): Response
    {
        $exam = $examRepository->findForUser($id, $this->getUser());
        
        if (!$exam) {
            throw $this->createNotFoundException('Экзамен не найден');
        }

        $schedules = $scheduleRepository->findByExam($exam);

        $allQuestions = [];
        foreach ($exam->getMaterials() as $material) {
            foreach ($material->getQuestions() as $question) {
                $allQuestions[$question->getId()] = $question;
            }
        }

        return $this->render('exams/schedule.html.twig', [
            'exam' => $exam,
            'schedules' => $schedules,
            'allQuestions' => $allQuestions,
        ]);
    }

    #[Route('/schedule/{id}/complete', name: 'app_schedule_complete', methods: ['POST'])]
    public function completeSchedule(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        StudySchedule $schedule,
        XpService $xpService,
        QuestionRepository $questionRepository
    ): Response {
        if ($schedule->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('schedule_action', $token)) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $schedule->setIsCompleted(true);
        
        $questionIds = $schedule->getQuestionIds() ?? [];
        if (!empty($questionIds)) {
            $questions = $questionRepository->findByIds($questionIds);
            foreach ($questions as $question) {
                $question->setStudied(true);
            }
        }
        
        $entityManager->flush();

        $user = $this->getUser();
        $xpResult = $xpService->awardXp($user, 30, 'schedule_day_completed');
        
        if ($xpResult['leveledUp']) {
            $this->addFlash('success', "🎉 Поздравляем! Вы достигли уровня {$xpResult['newLevel']} ({$xpResult['title']}) и получили +{$xpResult['xpAdded']} XP!");
        } else {
            $this->addFlash('success', "Занятие и все его вопросы отмечены как выученные! +{$xpResult['xpAdded']} XP (Всего: {$xpResult['xp']} XP)");
        }

        return $this->redirectToRoute('app_exam_schedule', ['id' => $schedule->getExam()->getId()]);
    }

    #[Route('/schedule/{id}/uncomplete', name: 'app_schedule_uncomplete', methods: ['POST'])]
    public function uncompleteSchedule(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        StudySchedule $schedule,
        QuestionRepository $questionRepository
    ): Response {
        if ($schedule->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('schedule_action', $token)) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $schedule->setIsCompleted(false);
        
        $questionIds = $schedule->getQuestionIds() ?? [];
        if (!empty($questionIds)) {
            $questions = $questionRepository->findByIds($questionIds);
            foreach ($questions as $question) {
                $question->setStudied(false);
            }
        }
        
        $entityManager->flush();

        $this->addFlash('success', 'Занятие возвращено в невыполненные');
        return $this->redirectToRoute('app_exam_schedule', ['id' => $schedule->getExam()->getId()]);
    }

    #[Route('/exam/{id}/delete', name: 'app_exam_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager, ExamRepository $examRepository): Response
    {
        $exam = $examRepository->findForUser($id, $this->getUser());
        
        if (!$exam) {
            throw $this->createNotFoundException('Экзамен не найден');
        }

        if (!$this->isCsrfTokenValid('delete_exam_' . $exam->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $entityManager->remove($exam);
        $entityManager->flush();

        $this->addFlash('success', 'Экзамен удалён');
        return $this->redirectToRoute('app_exams');
    }
}