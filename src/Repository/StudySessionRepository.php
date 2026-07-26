<?php

namespace App\Repository;

use App\Entity\StudySession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class StudySessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudySession::class);
    }

    public function countTodaySessions($user): int
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        try {
            return (int) $this->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->andWhere('s.user = :user')
                ->andWhere('s.finishedAt >= :today AND s.finishedAt < :tomorrow')
                ->setParameter('user', $user)
                ->setParameter('today', $today)
                ->setParameter('tomorrow', $tomorrow)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        }
    }
}