<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Entity\StudyTask;
use App\Entity\Avatar;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
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

    #[ORM\OneToMany(targetEntity: Exam::class, mappedBy: 'user')]
    private Collection $exams;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: StudyTask::class, orphanRemoval: true)]
    private Collection $studyTasks;

    #[ORM\OneToOne(targetEntity: Avatar::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Avatar $avatar = null;

    /**
     * @var Collection<int, ExamMaterial>
     */
    #[ORM\OneToMany(targetEntity: ExamMaterial::class, mappedBy: 'user')]
    private Collection $examMaterials;

    public function __construct()
    {
        $this->exams = new ArrayCollection();
        $this->studyTasks = new ArrayCollection();
        $this->examMaterials = new ArrayCollection();
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

    public function getExams(): Collection
    {
        return $this->exams;
    }

    public function addExam(Exam $exam): static
    {
        if (!$this->exams->contains($exam)) {
            $this->exams->add($exam);
            $exam->setUser($this);
        }
        return $this;
    }

    public function removeExam(Exam $exam): static
    {
        if ($this->exams->removeElement($exam)) {
            if ($exam->getUser() === $this) {
                $exam->setUser(null);
            }
        }
        return $this;
    }

    public function getStudyTasks(): Collection
    {
        return $this->studyTasks;
    }

    public function addStudyTask(StudyTask $studyTask): static
    {
        if (!$this->studyTasks->contains($studyTask)) {
            $this->studyTasks->add($studyTask);
            $studyTask->setUser($this);
        }
        return $this;
    }

    
    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    public function setAvatar(?Avatar $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function removeStudyTask(StudyTask $studyTask): static
    {
        if ($this->studyTasks->removeElement($studyTask)) {
            if ($studyTask->getUser() === $this) {
                $studyTask->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ExamMaterial>
     */
    public function getExamMaterials(): Collection
    {
        return $this->examMaterials;
    }

    public function addExamMaterial(ExamMaterial $examMaterial): static
    {
        if (!$this->examMaterials->contains($examMaterial)) {
            $this->examMaterials->add($examMaterial);
            $examMaterial->setUser($this);
        }

        return $this;
    }

    public function removeExamMaterial(ExamMaterial $examMaterial): static
    {
        if ($this->examMaterials->removeElement($examMaterial)) {
            // set the owning side to null (unless already changed)
            if ($examMaterial->getUser() === $this) {
                $examMaterial->setUser(null);
            }
        }

        return $this;
    }
}
