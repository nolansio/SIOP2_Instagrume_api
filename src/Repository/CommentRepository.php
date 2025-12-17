<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Publication;
use App\Entity\User;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository {

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine) {
        parent::__construct($doctrine, Comment::class);
        $this->doctrine = $doctrine;
    }

    public function create(string $content, ?User $user, Publication $publication, ?Comment $original_comment): Comment {
        $comment = new Comment();

        $comment->setContent($content);
        $comment->setPublication($publication);
        $comment->setOriginalComment($original_comment);
        $comment->setCreatedAt(new DateTimeImmutable("now", new DateTimeZone("Europe/Paris")));
        $comment->setUser($user);
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        return $comment;
    }

    public function update(Comment $comment, string $content): Comment {
        $comment->setContent($content);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        return $comment;
    }

    public function delete(Comment $comment): void {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($comment);
        $entityManager->flush();
    }

}
