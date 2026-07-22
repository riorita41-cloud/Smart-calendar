<?php

namespace App\Repository;

use App\Entity\XpLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class XpLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XpLog::class);
    }

    public function findLatestByUser(User $user, int $limit = 3): array
    {
        return $this->createQueryBuilder('x')
            ->where('x.user = :user')
            ->setParameter('user', $user)
            ->orderBy('x.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}