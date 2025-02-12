<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:"App\Repository\LikeRepository")]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity:Project::class, inversedBy:"likes")]
    #[ORM\JoinColumn(nullable:false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity:User::class)]
    #[ORM\JoinColumn(nullable:false)]
    private User $user;

    // Getters and setters...
    public function getId(): int
    {
        return $this->id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}