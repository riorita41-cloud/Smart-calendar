<?php

namespace App\Entity;

use App\Repository\ExamMaterialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExamMaterialRepository::class)]
class ExamMaterial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 10)]
    private ?string $fileType = 'manual';

    #[ORM\Column]
    private ?\DateTime $uploadedAt = null;

    #[ORM\ManyToOne(inversedBy: 'examMaterials')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Exam::class, inversedBy: 'materials')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Exam $exam = null;

    #[ORM\OneToMany(mappedBy: 'material', targetEntity: Question::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->uploadedAt = new \DateTime();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;
        return $this;
    }

    public function getUploadedAt(): ?\DateTime
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTime $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;
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

    public function getExam(): ?Exam
    {
        return $this->exam;
    }

    public function setExam(?Exam $exam): static
    {
        $this->exam = $exam;
        return $this;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setMaterial($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getMaterial() === $this) {
                $question->setMaterial(null);
            }
        }
        return $this;
    }
}