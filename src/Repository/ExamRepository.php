<?php

namespace App\Repository;

use App\Entity\Exam;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exam::class);
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function findByUserOrderedByDate(User $user): array
    {
        return $this->findBy(['user' => $user], ['examDate' => 'ASC']);
    }

    public function findForUser(int $id, User $user): ?Exam
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
    }
}