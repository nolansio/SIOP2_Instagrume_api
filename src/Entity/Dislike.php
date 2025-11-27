<?php

namespace App\Entity;

use App\Repository\DislikeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DislikeRepository::class)]
class Dislike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['public'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dislikes')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['public'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'dislikes')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(['public'])]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'dislikes')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(['public'])]
    private ?Comment $comment = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
