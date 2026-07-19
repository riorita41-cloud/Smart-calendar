<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $text = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $answer = null;

    #[ORM\Column]
    private int $orderNumber = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $studied = false;

    #[ORM\ManyToOne(targetEntity: ExamMaterial::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExamMaterial $material = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): static
    {
        $this->answer = $answer;
        return $this;
    }

    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(int $orderNumber): static
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function isStudied(): bool
    {
        return $this->studied;
    }

    public function setStudied(bool $studied): static
    {
        $this->studied = $studied;
        return $this;
    }

    public function getMaterial(): ?ExamMaterial
    {
        return $this->material;
    }

    public function setMaterial(?ExamMaterial $material): static
    {
        $this->material = $material;
        return $this;
    }
}