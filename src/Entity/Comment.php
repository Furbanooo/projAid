<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:"App\Repository\CommentRepository")]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private int $id;

    #[ORM\Column(type:"text")]
    private string $content;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity:Project::class, inversedBy:"comments")]
    #[ORM\JoinColumn(nullable:false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity:User::class)]
    #[ORM\JoinColumn(nullable:false)]
    private User $author;

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
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

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;
        return $this;
    }
}