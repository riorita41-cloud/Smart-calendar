<?php

namespace App\Controller;

use App\Repository\ExamRepository; 
use App\Repository\StudyTaskRepository;
use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(
        Request $request, 
        DashboardService $dashboardService, 
        StudyTaskRepository $studyTaskRepository,
        ExamRepository $examRepository // <-- Добавлен в параметры
    ): Response {
        $user = $this->getUser();
        $data = $dashboardService->getDashboardData($user);
        
        $tasksByDay = $studyTaskRepository->findTasksGroupedByDate($user);
        
        $year = $request->query->getInt('year', (int)date('Y'));
        $month = $request->query->getInt('month', (int)date('n'));
        $currentMonth = new \DateTimeImmutable("$year-$month-01");
        
        $today = new \DateTimeImmutable();
        $todayKey = $today->format('Y-m-d');
        
        $examDates = [];
        $examByDate = [];
        foreach ($data['exams'] as $exam) {
            $dateKey = $exam->getExamDate()->format('Y-m-d');
            $examDates[] = $dateKey;
            $examByDate[$dateKey] = $exam->getName();
        }
        
        $studyQuestionsByDay = [];

        foreach ($data['exams'] as $exam) {
            $schedules = $exam->getStudySchedules();
    
            $allQuestions = $examRepository->getAllQuestionsIndexed($exam);
    
            foreach ($schedules as $schedule) {
                $dateKey = $schedule->getStudyDate()->format('Y-m-d');
                $questionIds = $schedule->getQuestionIds() ?? [];
        
                if (!isset($studyQuestionsByDay[$dateKey])) {
                    $studyQuestionsByDay[$dateKey] = [];
                }
        
                foreach ($questionIds as $qId) {
                    if (isset($allQuestions[$qId])) {
                        $question = $allQuestions[$qId];
                        $studyQuestionsByDay[$dateKey][] = [
                            'id' => $question->getId(),
                            'text' => $question->getText(),
                            'answer' => $question->getAnswer(),
                            'examName' => $exam->getName(),
                            'completed' => $question->isStudied(),
                        ];
                    }
                }
            }
        }
        
        $monthNames = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];
        
        return $this->render('calendar/index.html.twig', [
            'exams'          => $data['exams'],
            'examDates'      => $examDates,
            'examByDate'     => $examByDate,
            'tasksByDay'     => $tasksByDay,
            'currentYear'    => (int)$currentMonth->format('Y'),
            'month'          => (int)$currentMonth->format('n'),
            'monthTitle'     => $monthNames[(int)$currentMonth->format('n')] . ' ' . $currentMonth->format('Y'),
            'daysInMonth'    => (int)$currentMonth->format('t'),
            'firstDayOfWeek' => (int)$currentMonth->format('N'),
            'progress'       => $data['progress'],
            'todayTasks'     => $data['todayTasks'],
            'today'          => $today,
            'prevMonth'      => $currentMonth->modify('-1 month'),
            'nextMonth'      => $currentMonth->modify('+1 month'),
            'studyQuestionsByDay' => $studyQuestionsByDay, 
        ]);
    }
}