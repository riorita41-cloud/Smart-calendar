<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\StudyTask;
use App\Entity\Avatar;
use App\Entity\StudySchedule;
use App\Entity\XpLog;
use App\Entity\StudySession;
use App\Entity\Exam;
use App\Entity\ExamMaterial;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')] 
#[UniqueEntity(fields: ['email'], message: 'Пользователь с таким email уже существует')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $xp = 0;

    #[ORM\Column(options: ['default' => 1])]
    private int $level = 1;

    #[ORM\Column(options: ['default' => 0])]
    private int $streakDays = 0;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $lastActivityDate = null;


    #[ORM\OneToMany(mappedBy: 'user', targetEntity: XpLog::class, cascade: ['persist', 'remove'])]
    private Collection $xpLogs;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: StudySession::class, cascade: ['persist', 'remove'])]
    private Collection $studySessions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Exam::class, cascade: ['persist', 'remove'])]
    private Collection $exams;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: StudyTask::class, orphanRemoval: true)]
    private Collection $studyTasks;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Avatar::class, cascade: ['persist', 'remove'])]
    private ?Avatar $avatar = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ExamMaterial::class, cascade: ['persist', 'remove'])]
    private Collection $examMaterials;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: StudySchedule::class, cascade: ['persist', 'remove'])]
    private Collection $studySchedules;

    public function __construct()
    {
        $this->exams = new ArrayCollection();
        $this->studyTasks = new ArrayCollection();
        $this->examMaterials = new ArrayCollection();
        $this->xpLogs = new ArrayCollection();
        $this->studySessions = new ArrayCollection();
        $this->studySchedules = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }


    public function getXp(): int
    {
        return $this->xp;
    }

    public function setXp(int $xp): static
    {
        $this->xp = $xp;
        return $this;
    }

    public function addXp(int $amount): static
    {
        $this->xp += $amount;
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function getStreakDays(): int
    {
        return $this->streakDays;
    }

    public function setStreakDays(int $streakDays): static
    {
        $this->streakDays = $streakDays;
        return $this;
    }

    public function getLastActivityDate(): ?\DateTimeInterface
    {
        return $this->lastActivityDate;
    }

    public function setLastActivityDate(?\DateTimeInterface $lastActivityDate): static
    {
        $this->lastActivityDate = $lastActivityDate;
        return $this;
    }


    public function getXpLogs(): Collection
    {
        return $this->xpLogs;
    }

    public function addXpLog(XpLog $xpLog): static
    {
        if (!$this->xpLogs->contains($xpLog)) {
            $this->xpLogs->add($xpLog);
            $xpLog->setUser($this);
        }
        return $this;
    }

    public function removeXpLog(XpLog $xpLog): static
    {
        if ($this->xpLogs->removeElement($xpLog)) {
            if ($xpLog->getUser() === $this) {
                $xpLog->setUser(null);
            }
        }
        return $this;
    }


    public function getExams(): Collection { return $this->exams; }
    public function addExam(Exam $exam): static {
        if (!$this->exams->contains($exam)) { $this->exams->add($exam); $exam->setUser($this); }
        return $this;
    }
    public function removeExam(Exam $exam): static {
        if ($this->exams->removeElement($exam)) { if ($exam->getUser() === $this) { $exam->setUser(null); } }
        return $this;
    }

    public function getStudyTasks(): Collection { return $this->studyTasks; }
    public function addStudyTask(StudyTask $studyTask): static {
        if (!$this->studyTasks->contains($studyTask)) { $this->studyTasks->add($studyTask); $studyTask->setUser($this); }
        return $this;
    }
    public function removeStudyTask(StudyTask $studyTask): static {
        if ($this->studyTasks->removeElement($studyTask)) { if ($studyTask->getUser() === $this) { $studyTask->setUser(null); } }
        return $this;
    }
    
    public function getAvatar(): ?Avatar { return $this->avatar; }
    public function setAvatar(?Avatar $avatar): static { $this->avatar = $avatar; return $this; }

    public function getExamMaterials(): Collection { return $this->examMaterials; }
    public function addExamMaterial(ExamMaterial $examMaterial): static {
        if (!$this->examMaterials->contains($examMaterial)) { $this->examMaterials->add($examMaterial); $examMaterial->setUser($this); }
        return $this;
    }
    public function removeExamMaterial(ExamMaterial $examMaterial): static {
        if ($this->examMaterials->removeElement($examMaterial)) { if ($examMaterial->getUser() === $this) { $examMaterial->setUser(null); } }
        return $this;
    }

    public function getStudySessions(): Collection { return $this->studySessions; }
    public function addStudySession(StudySession $studySession): static {
        if (!$this->studySessions->contains($studySession)) { $this->studySessions->add($studySession); $studySession->setUser($this); }
        return $this;
    }
    public function removeStudySession(StudySession $studySession): static {
        if ($this->studySessions->removeElement($studySession)) { if ($studySession->getUser() === $this) { $studySession->setUser(null); } }
        return $this;
    }

    public function getStudySchedules(): Collection { return $this->studySchedules; }
    public function addStudySchedule(StudySchedule $studySchedule): static {
        if (!$this->studySchedules->contains($studySchedule)) { $this->studySchedules->add($studySchedule); $studySchedule->setUser($this); }
        return $this;
    }
    public function removeStudySchedule(StudySchedule $studySchedule): static {
        if ($this->studySchedules->removeElement($studySchedule)) { if ($studySchedule->getUser() === $this) { $studySchedule->setUser(null); } }
        return $this;
    }
}