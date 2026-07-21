<?php

namespace App\Repository;

use App\Entity\Exam;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        public function getAllQuestionsIndexed(Exam $exam): array
    {
        $questions = [];
        foreach ($exam->getMaterials() as $material) {
            foreach ($material->getQuestions() as $question) {
                $questions[$question->getId()] = $question;
            }
        }
        return $questions;
    }
    public function findForUserOrThrow(int $id, User $user): Exam
    {
        $exam = $this->findForUser($id, $user);
        if (!$exam) {
            throw new NotFoundHttpException('Экзамен не найден или доступ запрещен');
        }
        return $exam;
    }
}