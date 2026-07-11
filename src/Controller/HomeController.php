<?php

namespace App\Controller;

use App\Repository\ExamRepository;
use App\Repository\StudyTaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
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
            if ($task->getScheduledDate()->format('Y-m-d') === $today->format('Y-m-d')) {
                $todayTasks[] = $task;
            }
        }
        
        return $this->render('home/index.html.twig', [
            'avatar' => $user ? $user->getAvatar() : null,
            'exams' => $exams,
            'tasks' => $tasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'progress' => $progress,
            'nearestExam' => $nearestExam,
            'daysToExam' => $daysToExam,
            'todayTasks' => $todayTasks,
        ]);
    }
}