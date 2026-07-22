<?php

namespace App\Service;

use App\Repository\ExamRepository;
use App\Repository\StudyTaskRepository;
use App\Repository\XpLogRepository; 
use App\Entity\User;

class DashboardService
{
    private ExamRepository $examRepository;
    private StudyTaskRepository $taskRepository;
    private XpLogRepository $xpLogRepository; 

    public function __construct(
        ExamRepository $examRepository,
        StudyTaskRepository $taskRepository,
        XpLogRepository $xpLogRepository 
    ) {
        $this->examRepository = $examRepository;
        $this->taskRepository = $taskRepository;
        $this->xpLogRepository = $xpLogRepository; 
    }

    public function getDashboardData(User $user): array
    {
        $exams = $this->examRepository->findByUser($user);
        $tasks = $this->taskRepository->findByUser($user);
        $today = new \DateTimeImmutable();  
        $todayKey = $today->format('Y-m-d');

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
            if ($task->getScheduledDate() && $task->getScheduledDate()->format('Y-m-d') === $todayKey) {
                $todayTasks[] = $task;
            }
        }

        $todaySessionsCount = 0;
        $sessions = $user->getStudySessions();
        if ($sessions) {
            foreach ($sessions as $session) {
                if ($session->isCompleted() && $session->getFinishedAt() && $session->getFinishedAt()->format('Y-m-d') === $todayKey) {
                    $todaySessionsCount++;
                }
            }
        }

        $level = $user->getLevel() ?? 1;
        $titles = [
            1 => 'Новичок', 2 => 'Ученик', 3 => 'Старательный', 4 => 'Знающий',
            5 => 'Подготовленный', 6 => 'Уверенный', 7 => 'Эксперт', 8 => 'Мастер',
            9 => 'Гуру', 10 => 'Легенда'
        ];
        $currentTitle = $titles[$level] ?? 'Легенда';

        $prevLevelXp = (($level - 1) ** 2) * 100;
        $nextLevelXp = ($level ** 2) * 100;
        $levelRange = $nextLevelXp - $prevLevelXp;
        $currentLevelXp = $user->getXp() - $prevLevelXp;
        $xpPercent = $levelRange > 0 ? (int)round(($currentLevelXp / $levelRange) * 100) : 100;

        $recentXpLogs = $this->xpLogRepository->findLatestByUser($user, 3);

        return [
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
            'todaySessionsCount' => $todaySessionsCount,
            'currentTitle' => $currentTitle,
            'xpPercent' => $xpPercent,
            'nextLevelXp' => $nextLevelXp,
            'recentXpLogs' => $recentXpLogs, 
        ];
    }
}