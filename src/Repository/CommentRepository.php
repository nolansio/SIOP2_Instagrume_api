<?php

namespace App\Repository;

use App\Entity\Comment;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository {

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $registry, ManagerRegistry $doctrine) {
        parent::__construct($registry, Comment::class);
        $this->doctrine = $doctrine;
    }

    public function create($content, $user, $publication, $original_comment): Comment {
        $comment = new Comment();

        $comment->setContent($content);
        $comment->setPublication($publication);
        $comment->setOriginalComment($original_comment);
        $comment->setCreatedAt(new DateTimeImmutable("now"));
        $comment->setUser($user);

        $entityManager = $this->doctrine->getManager();

        $entityManager->persist($comment);
        $entityManager->flush();

        return $comment;
    }

}
