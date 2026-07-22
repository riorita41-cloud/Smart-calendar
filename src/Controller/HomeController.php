<?php

namespace App\Controller;

use App\Entity\StudySession;
use App\Service\DashboardService;
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
    public function index(DashboardService $dashboardService): Response
    {
        $user = $this->getUser();
        $data = $dashboardService->getDashboardData($user);
        
        return $this->render('home/index.html.twig', [
            'user' => $user, 
            'avatar' => $user ? $user->getAvatar() : null,
            'exams' => $data['exams'],
            'tasks' => $data['tasks'],
            'totalTasks' => $data['totalTasks'],
            'completedTasks' => $data['completedTasks'],
            'progress' => $data['progress'],
            'totalQuestions' => $data['totalQuestions'],
            'studiedQuestions' => $data['studiedQuestions'],
            'nearestExam' => $data['nearestExam'],
            'daysToExam' => $data['daysToExam'],
            'todayTasks' => $data['todayTasks'],
            'todaySessionsCount' => $data['todaySessionsCount'],
            'currentTitle' => $data['currentTitle'],
            'xpPercent' => $data['xpPercent'],
            'nextLevelXp' => $data['nextLevelXp'],
            'recentXpLogs' => $data['recentXpLogs'],
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

        $token = $request->headers->get('X-CSRF-TOKEN');
        
        if (!$token || !$this->isCsrfTokenValid('task_action', $token)) {
            return new JsonResponse(['error' => 'Неверный токен безопасности'], 403);
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