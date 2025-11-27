<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['public'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['public'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['public'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(['public'])]
    private ?self $original_comment = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'original_comment', orphanRemoval: true)]
    #[Groups(['public'])]
    private Collection $comments;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'comment', orphanRemoval: true)]
    #[Groups(['public'])]
    private Collection $likes;

    /**
     * @var Collection<int, Dislike>
     */
    #[ORM\OneToMany(targetEntity: Dislike::class, mappedBy: 'comment', orphanRemoval: true)]
    #[Groups(['public'])]
    private Collection $dislikes;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['public'])]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['public'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->dislikes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getOriginalComment(): ?self
    {
        return $this->original_comment;
    }

    public function setOriginalComment(?self $original_comment): static
    {
        $this->original_comment = $original_comment;
        $this->setPost($original_comment->getPost());

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(self $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setOriginalComment($this);
            $comment->setPost($this->getPost());
        }

        return $this;
    }

    public function removeComment(self $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getOriginalComment() === $this) {
                $comment->setOriginalComment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setComment($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getComment() === $this) {
                $like->setComment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Dislike>
     */
    public function getDislikes(): Collection
    {
        return $this->dislikes;
    }

    public function addDislike(Dislike $dislike): static
    {
        if (!$this->dislikes->contains($dislike)) {
            $this->dislikes->add($dislike);
            $dislike->setComment($this);
        }

        return $this;
    }

    public function removeDislike(Dislike $dislike): static
    {
        if ($this->dislikes->removeElement($dislike)) {
            // set the owning side to null (unless already changed)
            if ($dislike->getComment() === $this) {
                $dislike->setComment(null);
            }
        }

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

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
}
