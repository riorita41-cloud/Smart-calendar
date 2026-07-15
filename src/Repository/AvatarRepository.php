<?php

namespace App\Repository;

use App\Entity\Avatar;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class AvatarRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Avatar::class);
        $this->entityManager = $entityManager;
    }

    public function findOrCreateForUser(User $user): Avatar
    {
        $avatar = $this->findOneBy(['user' => $user]);

        if (!$avatar) {
            $avatar = new Avatar();
            $avatar->setUser($user);
            $avatar->setSeed($user->getEmail());
            $avatar->setSkinColor('edb98a');
            $avatar->setHairColor('724133');
            $avatar->setHairStyle('long01');
            
            $this->entityManager->persist($avatar);
            $this->entityManager->flush();
        }

        return $avatar;
    }
}