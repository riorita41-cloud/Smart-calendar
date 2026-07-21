<?php

namespace App\Repository;

use App\Entity\ExamMaterial;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExamMaterialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExamMaterial::class);
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function findByUserOrderedByUploadDate(User $user): array
    {
        return $this->findBy(['user' => $user], ['uploadedAt' => 'DESC']);
    }

    public function findForUser(int $id, User $user): ?ExamMaterial
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
    }

    public function findForUserOrThrow(int $id, User $user): ExamMaterial
    {
        $material = $this->findForUser($id, $user);
        if (!$material) {
            throw new NotFoundHttpException('Материал не найден или доступ запрещен');
        }
        return $material;
    }

}