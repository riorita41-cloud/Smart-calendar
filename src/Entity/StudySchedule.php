<?php

namespace App\Entity;

use App\Repository\StudyScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudyScheduleRepository::class)]
class StudySchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Exam::class, inversedBy: 'studySchedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Exam $exam = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'studySchedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTime $studyDate = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTime $startTime = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTime $endTime = null;

    #[ORM\Column]
    private int $questionsCount = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $questionIds = null;

    #[ORM\Column]
    private bool $isCompleted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExam(): ?Exam
    {
        return $this->exam;
    }

    public function setExam(?Exam $exam): static
    {
        $this->exam = $exam;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getStudyDate(): ?\DateTime
    {
        return $this->studyDate;
    }

    public function setStudyDate(?\DateTime $studyDate): static
    {
        $this->studyDate = $studyDate;
        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTime $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTime $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getQuestionsCount(): int
    {
        return $this->questionsCount;
    }

    public function setQuestionsCount(int $questionsCount): static
    {
        $this->questionsCount = $questionsCount;
        return $this;
    }

    public function getQuestionIds(): ?array
    {
        return $this->questionIds;
    }

    public function setQuestionIds(?array $questionIds): static
    {
        $this->questionIds = $questionIds;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;
        return $this;
    }
}