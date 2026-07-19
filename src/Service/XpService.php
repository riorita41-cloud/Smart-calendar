<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\XpLog;
use Doctrine\ORM\EntityManagerInterface;

class XpService
{
    private EntityManagerInterface $em;

    private const TITLES = [
        1 => 'Новичок',
        2 => 'Ученик',
        3 => 'Старательный',
        4 => 'Знающий',
        5 => 'Подготовленный',
        6 => 'Уверенный',
        7 => 'Эксперт',
        8 => 'Мастер',
        9 => 'Гуру',
        10 => 'Легенда',
    ];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function awardXp(User $user, int $amount, string $reason): array
    {
        $oldXp = $user->getXp();
        $user->addXp($amount);
        
        $oldLevel = $user->getLevel();
        $newLevel = $this->calculateLevel($user->getXp());
        $leveledUp = false;

        if ($newLevel > $oldLevel) {
            $user->setLevel($newLevel);
            $leveledUp = true;
        }

        $log = new XpLog();
        $log->setUser($user);
        $log->setAmount($amount);
        $log->setReason($reason);
        $this->em->persist($log);

        $this->updateStreak($user);

        $this->em->flush();

        return [
            'leveledUp' => $leveledUp,
            'oldLevel' => $oldLevel,
            'newLevel' => $newLevel,
            'title' => $this->getTitle($newLevel),
            'xp' => $user->getXp(),
            'xpAdded' => $amount,
            'xpToNextLevel' => $this->getXpForNextLevel($newLevel) - $user->getXp(),
        ];
    }

    private function calculateLevel(int $xp): int
    {
        if ($xp <= 0) {
            return 1;
        }
        
        $level = 1 + (int) floor(sqrt($xp / 100));
        
        return min($level, 10); 
    }

    public function getTitle(int $level): string
    {
        return self::TITLES[$level] ?? end(self::TITLES);
    }

    public function getXpForNextLevel(int $currentLevel): int
    {
        $nextLevel = $currentLevel + 1;
        return (int) pow($nextLevel - 1, 2) * 100;
    }

    private function updateStreak(User $user): void
    {
        $today = new \DateTime('today'); 
        $lastActivity = $user->getLastActivityDate();

        if ($lastActivity === null) {
            $user->setLastActivityDate($today);
            $user->setStreakDays(1);
            return;
        }

        if ($lastActivity instanceof \DateTimeImmutable) {
            $lastDate = \DateTime::createFromImmutable($lastActivity);
        } else {
            $lastDate = clone $lastActivity;
        }
        
        $diff = $lastDate->diff($today)->days;

        if ($diff === 0) {
            return;
        } elseif ($diff === 1) {
            $user->setStreakDays($user->getStreakDays() + 1);
            $user->setLastActivityDate($today);
        } else {
            $user->setStreakDays(1);
            $user->setLastActivityDate($today);
        }
    }
}