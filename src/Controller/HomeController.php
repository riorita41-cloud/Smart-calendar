<?php

namespace App\Controller;

use App\Entity\StudySession;
use App\Repository\ExamRepository;
use App\Repository\StudyTaskRepository;
use App\Service\XpService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(ExamRepository $examRepository, StudyTaskRepository $studyTaskRepository): Response
    {
        $user = $this->getUser();
        
        $exams = $examRepository->findBy(['user' => $user]);
        $tasks = $studyTaskRepository->findBy(['user' => $user]);
        
        $totalTasks = count($tasks);
        $completedTasks = 0;
        foreach ($tasks as $task) {
            if ($task->isCompleted()) {
                $completedTasks++;
            }
        }
        
        $totalQuestions = 0;
        $studiedQuestions = 0;
        
        foreach ($exams as $exam) {
            foreach ($exam->getMaterials() as $material) {
                foreach ($material->getQuestions() as $question) {
                    $totalQuestions++;
                    if ($question->isStudied()) {
                        $studiedQuestions++;
                    }
                }
            }
        }
        
        $progress = $totalQuestions > 0 ? round(($studiedQuestions / $totalQuestions) * 100) : 0;
        
        $nearestExam = null;
        $daysToExam = null;
        $today = new \DateTimeImmutable();
        
        foreach ($exams as $exam) {
            if ($exam->getExamDate() > $today) {
                $days = (int)(($exam->getExamDate()->getTimestamp() - $today->getTimestamp()) / 86400);
                if ($nearestExam === null || $days < $daysToExam) {
                    $nearestExam = $exam;
                    $daysToExam = $days;
                }
            }
        }
        
        $todayTasks = [];
        foreach ($tasks as $task) {
            if ($task->getScheduledDate() && $task->getScheduledDate()->format('Y-m-d') === $today->format('Y-m-d')) {
                $todayTasks[] = $task;
            }
        }
        
        return $this->render('home/index.html.twig', [
            'user' => $user, 
            'avatar' => $user ? $user->getAvatar() : null,
            'exams' => $exams,
            'tasks' => $tasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'progress' => $progress,
            'totalQuestions' => $totalQuestions,
            'studiedQuestions' => $studiedQuestions,
            'nearestExam' => $nearestExam,
            'daysToExam' => $daysToExam,
            'todayTasks' => $todayTasks,
        ]);
    }

    #[Route('/api/pomodoro/complete', name: 'api_pomodoro_complete', methods: ['POST'])]
    public function completePomodoro(
        Request $request,
        EntityManagerInterface $entityManager,
        XpService $xpService
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Не авторизован'], 401);
        }

        $session = new StudySession();
        $session->setUser($user);
        $session->setDurationMinutes(25);
        $session->setStartedAt(new \DateTimeImmutable('-25 minutes'));
        $session->setFinishedAt(new \DateTimeImmutable());
        $session->setIsCompleted(true);
        
        $entityManager->persist($session);

        $baseXp = 25;
        $bonusXp = 0;
        
        $requestData = json_decode($request->getContent(), true);
        if (isset($requestData['isLongBreakBonus']) && $requestData['isLongBreakBonus']) {
            $bonusXp = 10;
        }
        
        $totalXp = $baseXp + $bonusXp;
        $reason = $bonusXp > 0 ? 'pomodoro_full_cycle' : 'pomodoro_session';
        $xpResult = $xpService->awardXp($user, $totalXp, $reason);

        $entityManager->flush();

        $message = $bonusXp > 0 
            ? "Сессия завершена! +{$baseXp} XP + {$bonusXp} бонус!" 
            : 'Сессия завершена! +25 XP';

        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'xp' => $xpResult
        ]);
    }
}