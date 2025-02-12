<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:"App\Repository\ProjectRepository")]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private int $id;

    #[ORM\Column(type:"string", length:255)]
    private string $title;

    #[ORM\Column(type:"text")]
    private string $description;

    #[ORM\Column(type:"text", nullable:true)]
    private ?string $content = null;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type:"datetime", nullable:true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity:User::class, inversedBy:"initiatedProjects")]
    #[ORM\JoinColumn(nullable:false)]
    private User $initiator;

    #[ORM\ManyToMany(targetEntity:User::class, mappedBy:"contributedProjects")]
    private Collection $contributors;

    #[ORM\OneToMany(mappedBy:"project", targetEntity:Comment::class, cascade:["persist", "remove"])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy:"project", targetEntity:Like::class, cascade:["persist", "remove"])]
    private Collection $likes;

    #[ORM\Column(type:"string", length:255)]
    private string $name;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $creationDate;

    public function __construct()
    {
        $this->contributors = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getInitiator(): User
    {
        return $this->initiator;
    }

    public function setInitiator(User $initiator): self
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function getContributors(): Collection
    {
        return $this->contributors;
    }

    public function addContributor(User $contributor): self
    {
        if (!$this->contributors->contains($contributor)) {
            $this->contributors[] = $contributor;
            $contributor->addContributedProject($this);
        }
        return $this;
    }

    public function removeContributor(User $contributor): self
    {
        if ($this->contributors->removeElement($contributor)) {
            $contributor->removeContributedProject($this);
        }
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setProject($this);
        }
        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getProject() === $this) {
                $comment->setProject(null);
            }
        }
        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setProject($this);
        }
        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getProject() === $this) {
                $like->setProject(null);
            }
        }
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;
        return $this;
    }
}