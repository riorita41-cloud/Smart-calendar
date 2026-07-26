<?php

namespace App\Controller;

use App\Repository\StudySessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PomodoroController extends AbstractController
{
    #[Route('/pomodoro', name: 'app_pomodoro')]
    public function index(StudySessionRepository $sessionRepository): Response
    {
        $user = $this->getUser();
        $todaySessionsCount = $sessionRepository->countTodaySessions($user);
        
        return $this->render('pomodoro/index.html.twig', [
            'todaySessionsCount' => $todaySessionsCount,
        ]);
    }
}