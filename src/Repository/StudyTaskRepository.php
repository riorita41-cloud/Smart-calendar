<?php

namespace App\Repository;

use App\Entity\StudyTask;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudyTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudyTask::class);
    }

    public function findTasksGroupedByDate(User $user): array
    {
        $tasks = $this->findBy(['user' => $user]);
        $grouped = [];
        foreach ($tasks as $task) {
            $dateKey = $task->getScheduledDate()->format('Y-m-d');
            $grouped[$dateKey][] = $task;
        }
        return $grouped;
    }

    public function getTaskStats(User $user): array
    {
        $tasks = $this->findBy(['user' => $user]);
        $total = count($tasks);
        $completed = count(array_filter($tasks, fn($t) => $t->isCompleted()));
        return [
            'progress' => $total > 0 ? round(($completed / $total) * 100) : 0
        ];
    }
}