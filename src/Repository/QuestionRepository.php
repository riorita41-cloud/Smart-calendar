<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function findForUser(int $id, User $user): ?Question
    {
        $question = $this->find($id);
        
        if (!$question) {
            return null;
        }
        
        if ($question->getMaterial()->getUser() !== $user) {
            return null;
        }
        
        return $question;
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        
        return $this->findBy(['id' => $ids]);
    }
}