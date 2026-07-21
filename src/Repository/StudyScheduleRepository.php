<?php

namespace App\Repository;

use App\Entity\Exam;
use App\Entity\StudySchedule;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StudyScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudySchedule::class);
    }

    public function findByExam(Exam $exam): array
    {
        return $this->findBy(['exam' => $exam], ['studyDate' => 'ASC']);
    }

    public function findForUser(int $id, User $user): ?StudySchedule
    {
        $schedule = $this->find($id);
        
        if (!$schedule) {
            return null;
        }
        
        if ($schedule->getUser() !== $user) {
            return null;
        }
        
        return $schedule;
    }

    public function deleteByExam(Exam $exam): int
    {
        return $this->createQueryBuilder('s')
            ->delete()
            ->where('s.exam = :exam')
            ->setParameter('exam', $exam)
            ->getQuery()
            ->execute();
    }

    public function findForUserOrThrow(int $id, User $user): StudySchedule
    {
        $schedule = $this->findForUser($id, $user);
        if (!$schedule) {
            throw new NotFoundHttpException('Расписание не найдено или доступ запрещен');
        }
        return $schedule;
    }

}