<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Entity\StudySchedule;
use App\Form\ExamType;
use App\Service\ScheduleGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExamController extends AbstractController
{
    #[Route('/exams', name: 'app_exams')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        $exams = $entityManager->getRepository(Exam::class)->findBy(
            ['user' => $user],
            ['examDate' => 'ASC']
        );
        
        $materials = $entityManager->getRepository(\App\Entity\ExamMaterial::class)->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );
        
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
        
        return $this->render('exams/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/exam/{id}/generate-schedule', name: 'app_exam_generate_schedule', methods: ['POST'])]
    public function generateSchedule(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ScheduleGenerator $scheduleGenerator
    ): Response {
        $exam = $entityManager->getRepository(Exam::class)->find($id);
        
        if (!$exam || $exam->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Экзамен не найден');
        }
        
        // Проверка CSRF токена
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
    public function schedule(int $id, EntityManagerInterface $entityManager): Response
    {
        $exam = $entityManager->getRepository(Exam::class)->find($id);
        
        if (!$exam || $exam->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Экзамен не найден');
        }

        $schedules = $entityManager->getRepository(StudySchedule::class)->findBy(
            ['exam' => $exam],
            ['studyDate' => 'ASC']
        );

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
        StudySchedule $schedule
    ): Response {
        if ($schedule->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('schedule_action', $token)) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $schedule->setIsCompleted(true);
        $entityManager->flush();

        $this->addFlash('success', 'Занятие отмечено как выполненное!');
        return $this->redirectToRoute('app_exam_schedule', ['id' => $schedule->getExam()->getId()]);
    }

    #[Route('/schedule/{id}/uncomplete', name: 'app_schedule_uncomplete', methods: ['POST'])]
    public function uncompleteSchedule(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        StudySchedule $schedule
    ): Response {
        if ($schedule->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('schedule_action', $token)) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $schedule->setIsCompleted(false);
        $entityManager->flush();

        $this->addFlash('success', 'Занятие возвращено в невыполненные');
        return $this->redirectToRoute('app_exam_schedule', ['id' => $schedule->getExam()->getId()]);
    }

    #[Route('/exam/{id}/delete', name: 'app_exam_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $exam = $entityManager->getRepository(Exam::class)->find($id);
        
        if (!$exam || $exam->getUser() !== $this->getUser()) {
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