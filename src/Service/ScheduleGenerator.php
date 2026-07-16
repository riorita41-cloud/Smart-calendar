<?php

namespace App\Service;

use App\Entity\Exam;
use App\Entity\StudySchedule;
use App\Entity\ExamMaterial;
use Doctrine\ORM\EntityManagerInterface;

class ScheduleGenerator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generate(Exam $exam): array
    {
        $user = $exam->getUser();
        $examDate = $exam->getExamDate();
        $studyDays = $exam->getStudyDays();
        $startTime = $exam->getStudyStartTime();
        $endTime = $exam->getStudyEndTime();

        if (!$examDate || empty($studyDays) || !$startTime || !$endTime) {
            return ['error' => 'Заполните дату экзамена, дни и время занятий.'];
        }

        $totalQuestions = $this->countQuestionsInMaterials($exam);

        if ($totalQuestions === 0) {
            return ['error' => 'У вас нет материалов с вопросами для этого экзамена. Сначала добавьте вопросы в разделе "Материалы" и привяжите их к экзамену.'];
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        
        $examDay = \DateTime::createFromFormat('Y-m-d', $examDate->format('Y-m-d'));
        $examDay->setTime(0, 0, 0);
        
        $daysUntilExam = $today->diff($examDay)->days;

        if ($daysUntilExam <= 0) {
            return ['error' => 'Дата экзамена должна быть в будущем.'];
        }

        $availableDates = $this->getAvailableDates($today, $examDay, $studyDays);

        if (empty($availableDates)) {
            return ['error' => 'Нет доступных дней для занятий до даты экзамена. Проверьте выбранные дни недели.'];
        }

        $questionsPerDay = (int) ceil($totalQuestions / count($availableDates));
        $exam->setQuestionsPerDay($questionsPerDay);
        $this->entityManager->persist($exam);

        $oldSchedules = $this->entityManager->getRepository(StudySchedule::class)->findBy(['exam' => $exam]);
        foreach ($oldSchedules as $old) {
            $this->entityManager->remove($old);
        }

        $allQuestions = $this->getAllQuestionsForExam($exam);

        $questionIndex = 0;
        $createdSchedules = [];

        foreach ($availableDates as $date) {
            $questionsForDay = min($questionsPerDay, $totalQuestions - $questionIndex);
            if ($questionsForDay <= 0) break;

            $questionIds = [];
            for ($i = 0; $i < $questionsForDay; $i++) {
                if (isset($allQuestions[$questionIndex + $i])) {
                    $questionIds[] = $allQuestions[$questionIndex + $i]->getId();
                }
            }

            $schedule = new StudySchedule();
            $schedule->setExam($exam);
            $schedule->setUser($user);
            $schedule->setStudyDate($date);
            $schedule->setStartTime($startTime);
            $schedule->setEndTime($endTime);
            $schedule->setQuestionsCount($questionsForDay);
            $schedule->setIsCompleted(false);
            $schedule->setQuestionIds($questionIds);

            $this->entityManager->persist($schedule);
            $createdSchedules[] = $schedule;

            $questionIndex += $questionsForDay;
        }

        $this->entityManager->flush();

        return [
            'success' => true,
            'totalQuestions' => $totalQuestions,
            'daysUntilExam' => $daysUntilExam,
            'availableDaysCount' => count($availableDates),
            'questionsPerDay' => $questionsPerDay,
        ];
    }

    private function countQuestionsInMaterials(Exam $exam): int
    {
        $totalQuestions = 0;
        
        foreach ($exam->getMaterials() as $material) {
            $totalQuestions += count($material->getQuestions());
        }
        
        return $totalQuestions;
    }

    private function getAllQuestionsForExam(Exam $exam): array
    {
        $allQuestions = [];
        
        foreach ($exam->getMaterials() as $material) {
            foreach ($material->getQuestions() as $question) {
                $allQuestions[] = $question;
            }
        }
        
        usort($allQuestions, fn($a, $b) => $a->getOrderNumber() <=> $b->getOrderNumber());
        
        return $allQuestions;
    }

    private function getAvailableDates(\DateTime $today, \DateTime $examDay, array $studyDays): array
    {
        $daysMap = [
            'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7
        ];

        $availableDates = [];
        $currentDate = clone $today;
        $currentDate->modify('+1 day');

        while ($currentDate <= $examDay) {
            $dayOfWeekNumber = (int) $currentDate->format('N');
            $dayName = array_search($dayOfWeekNumber, $daysMap);

            if (in_array($dayName, $studyDays)) {
                $availableDates[] = clone $currentDate;
            }

            $currentDate = clone $currentDate;
            $currentDate->modify('+1 day');
        }

        return $availableDates;
    }
}