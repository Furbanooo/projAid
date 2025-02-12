<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private int $id;

    #[ORM\Column(type:"string", length:255, unique:true)]
    private string $username;

    #[ORM\Column(type:"string", length:255, unique:true)]
    private string $email;

    #[ORM\Column(type:"json")]
    private array $roles = ['ROLE_USER']; // Default role

    #[ORM\Column(type:"string", length:255)]
    private string $password;

    // Projects that this user has initiated
    #[ORM\OneToMany(mappedBy:"initiator", targetEntity:Project::class)]
    private Collection $initiatedProjects;

    // Projects that this user has contributed to
    #[ORM\ManyToMany(targetEntity:Project::class, inversedBy:"contributors")]
    #[ORM\JoinTable(name:"user_project")]
    private Collection $contributedProjects;

    public function __construct()
    {
        $this->initiatedProjects = new ArrayCollection();
        $this->contributedProjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getInitiatedProjects(): Collection
    {
        return $this->initiatedProjects;
    }

    public function addInitiatedProject(Project $project): self
    {
        if (!$this->initiatedProjects->contains($project)) {
            $this->initiatedProjects[] = $project;
            $project->setInitiator($this);
        }
        return $this;
    }

    public function removeInitiatedProject(Project $project): self
    {
        if ($this->initiatedProjects->removeElement($project)) {
            if ($project->getInitiator() === $this) {
                $project->setInitiator(null);
            }
        }
        return $this;
    }

    public function getContributedProjects(): Collection
    {
        return $this->contributedProjects;
    }

    public function addContributedProject(Project $project): self
    {
        if (!$this->contributedProjects->contains($project)) {
            $this->contributedProjects[] = $project;
        }
        return $this;
    }

    public function removeContributedProject(Project $project): self
    {
        $this->contributedProjects->removeElement($project);
        return $this;
    }
}