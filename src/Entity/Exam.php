<?php

namespace App\Entity;

use App\Repository\ExamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExamRepository::class)]
class Exam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $subject = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $examDate = null;

    #[ORM\ManyToOne(inversedBy: 'exams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $studyDays = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTime $studyStartTime = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTime $studyEndTime = null;

    #[ORM\Column]
    private int $questionsPerDay = 0;

    #[ORM\OneToMany(mappedBy: 'exam', targetEntity: StudySchedule::class, cascade: ['persist', 'remove'])]
    private Collection $studySchedules;

    #[ORM\OneToMany(mappedBy: 'exam', targetEntity: StudyTask::class, cascade: ['remove'])]
    private Collection $studyTasks;

    #[ORM\OneToMany(mappedBy: 'exam', targetEntity: ExamMaterial::class)]
    private Collection $materials;

    public function __construct()
    {
        $this->studySchedules = new ArrayCollection();
        $this->studyTasks = new ArrayCollection();
        $this->materials = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getExamDate(): ?\DateTimeImmutable
    {
        return $this->examDate;
    }

    public function setExamDate(\DateTimeImmutable $examDate): static
    {
        $this->examDate = $examDate;
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

    public function getStudyDays(): ?array
    {
        return $this->studyDays;
    }

    public function setStudyDays(?array $studyDays): static
    {
        $this->studyDays = $studyDays;
        return $this;
    }

    public function getStudyStartTime(): ?\DateTime
    {
        return $this->studyStartTime;
    }

    public function setStudyStartTime(?\DateTime $studyStartTime): static
    {
        $this->studyStartTime = $studyStartTime;
        return $this;
    }

    public function getStudyEndTime(): ?\DateTime
    {
        return $this->studyEndTime;
    }

    public function setStudyEndTime(?\DateTime $studyEndTime): static
    {
        $this->studyEndTime = $studyEndTime;
        return $this;
    }

    public function getQuestionsPerDay(): int
    {
        return $this->questionsPerDay;
    }

    public function setQuestionsPerDay(int $questionsPerDay): static
    {
        $this->questionsPerDay = $questionsPerDay;
        return $this;
    }

    public function getStudySchedules(): Collection
    {
        return $this->studySchedules;
    }

    public function addStudySchedule(StudySchedule $schedule): static
    {
        if (!$this->studySchedules->contains($schedule)) {
            $this->studySchedules->add($schedule);
            $schedule->setExam($this);
        }
        return $this;
    }

    public function removeStudySchedule(StudySchedule $schedule): static
    {
        if ($this->studySchedules->removeElement($schedule)) {
            if ($schedule->getExam() === $this) {
                $schedule->setExam(null);
            }
        }
        return $this;
    }

    public function getStudyTasks(): Collection
    {
        return $this->studyTasks;
    }

    public function addStudyTask(StudyTask $task): static
    {
        if (!$this->studyTasks->contains($task)) {
            $this->studyTasks->add($task);
            $task->setExam($this);
        }
        return $this;
    }

    public function removeStudyTask(StudyTask $task): static
    {
        if ($this->studyTasks->removeElement($task)) {
            if ($task->getExam() === $this) {
                $task->setExam(null);
            }
        }
        return $this;
    }

    public function getMaterials(): Collection
    {
        return $this->materials;
    }

    public function addMaterial(ExamMaterial $material): static
    {
        if (!$this->materials->contains($material)) {
            $this->materials->add($material);
            $material->setExam($this);
        }
        return $this;
    }

    public function removeMaterial(ExamMaterial $material): static
    {
        if ($this->materials->removeElement($material)) {
            if ($material->getExam() === $this) {
                $material->setExam(null);
            }
        }
        return $this;
    }

    public function getDaysUntilExam(): int
    {
        if (!$this->examDate) {
            return 0;
        }

        $today = new \DateTimeImmutable();
        $today = $today->setTime(0, 0, 0);
        $examDay = $this->examDate->setTime(0, 0, 0);

        return $today->diff($examDay)->days;
    }

    public function getStudyDaysFormatted(): array
    {
        $daysMap = [
            'monday' => 'Пн',
            'tuesday' => 'Вт',
            'wednesday' => 'Ср',
            'thursday' => 'Чт',
            'friday' => 'Пт',
            'saturday' => 'Сб',
            'sunday' => 'Вс',
        ];

        $result = [];
        foreach ($this->studyDays ?? [] as $day) {
            $result[] = $daysMap[$day] ?? $day;
        }

        return $result;
    }
}