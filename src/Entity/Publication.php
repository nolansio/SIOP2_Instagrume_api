<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['all', 'publication', 'user'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['all', 'publication', 'user'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['all', 'publication', 'user'])]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'publication', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['all', 'publication', 'user'])]
    private Collection $images;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'publication', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['all', 'publication', 'user'])]
    private Collection $likes;

    /**
     * @var Collection<int, Dislike>
     */
    #[ORM\OneToMany(targetEntity: Dislike::class, mappedBy: 'publication', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['all', 'publication', 'user'])]
    private Collection $dislikes;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[ORM\JoinColumn(onDelete: "CASCADE", nullable: false)]
    #[Groups(['all', 'publication'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'publication', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['all', 'publication', 'user'])]
    private Collection $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->dislikes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setPublication($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getPublication() === $this) {
                $image->setPublication(null);
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
            $like->setPublication($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getPublication() === $this) {
                $like->setPublication(null);
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
            $dislike->setPublication($this);
        }

        return $this;
    }

    public function removeDislike(Dislike $dislike): static
    {
        if ($this->dislikes->removeElement($dislike)) {
            // set the owning side to null (unless already changed)
            if ($dislike->getPublication() === $this) {
                $dislike->setPublication(null);
            }
        }

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

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPublication($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPublication() === $this) {
                $comment->setPublication(null);
            }
        }

        return $this;
    }
}
