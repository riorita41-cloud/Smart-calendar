<?php

namespace App\Controller;

use App\Repository\ExamRepository;
use App\Repository\StudyTaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(Request $request, ExamRepository $examRepository, StudyTaskRepository $studyTaskRepository): Response
    {
        $user = $this->getUser();
        
        $exams = $examRepository->findBy(['user' => $user]);
        $tasks = $studyTaskRepository->findBy(['user' => $user]);
        
        $year = $request->query->get('year');
        $month = $request->query->get('month');
        
        if ($year && $month) {
            $currentMonth = new \DateTimeImmutable($year . '-' . $month . '-01');
        } else {
            $currentMonth = new \DateTimeImmutable('first day of this month');
        }
        
        $currentYear = (int)$currentMonth->format('Y');
        $monthNum = (int)$currentMonth->format('n');
        $daysInMonth = (int)$currentMonth->format('t');
        $firstDayOfWeek = (int)$currentMonth->format('N');
        
        $prevMonth = $currentMonth->modify('-1 month');
        $nextMonth = $currentMonth->modify('+1 month');
        
        $monthNames = [
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь'
        ];
        
        $monthTitle = $monthNames[$monthNum] . ' ' . $currentYear;
        
        $tasksByDay = [];
        foreach ($tasks as $task) {
            $dateKey = $task->getScheduledDate()->format('Y-m-d');
            if (!isset($tasksByDay[$dateKey])) {
                $tasksByDay[$dateKey] = [];
            }
            $tasksByDay[$dateKey][] = $task;
        }
        
        $totalTasks = count($tasks);
        $completedTasks = 0;
        foreach ($tasks as $task) {
            if ($task->isCompleted()) {
                $completedTasks++;
            }
        }
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        $progressBySubject = [];
        foreach ($exams as $exam) {
            $examTasks = array_filter($tasks, fn($task) => $task->getExam() === $exam);
            $examTotal = count($examTasks);
            $examCompleted = 0;
            foreach ($examTasks as $task) {
                if ($task->isCompleted()) {
                    $examCompleted++;
                }
            }
            $progressBySubject[$exam->getSubject()] = $examTotal > 0 ? round(($examCompleted / $examTotal) * 100) : 0;
        }
        
        $today = new \DateTimeImmutable();
        $todayTasks = $tasksByDay[$today->format('Y-m-d')] ?? [];
        
        return $this->render('calendar/index.html.twig', [
            'exams' => $exams,
            'tasks' => $tasks,
            'tasksByDay' => $tasksByDay,
            'currentYear' => $currentYear,
            'month' => $monthNum,
            'monthTitle' => $monthTitle,
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'progress' => $progress,
            'progressBySubject' => $progressBySubject,
            'todayTasks' => $todayTasks,
            'today' => $today,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
}
