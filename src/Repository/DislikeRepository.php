<?php

namespace App\Repository;

use App\Entity\Dislike;
use App\Entity\User;
use App\Entity\Publication;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dislike>
 */
class DislikeRepository extends ServiceEntityRepository {

    public function __construct(private readonly ManagerRegistry $doctrine) {
        parent::__construct($doctrine, Dislike::class);
    }

    public function create(?User $user, ?Publication $publication, ?Comment $comment): Dislike {
        $entityManager = $this->doctrine->getManager();

        $dislike = new Dislike();
        $dislike->setUser($user);
        $dislike->setPublication($publication);
        $dislike->setComment($comment);

        $entityManager->persist($dislike);
        $entityManager->flush();
        return $dislike;
    }

    public function delete(Dislike $dislike): void {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($dislike);
        $entityManager->flush();
    }

    public function findDislikeByUserAndPublication(?User $user, Publication $publication): ?Dislike {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user_id')
            ->setParameter('user_id', $user->getId())
            ->andWhere('l.publication = :publication_id')
            ->setParameter('publication_id', $publication->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    public function findDislikeByUserAndComment(?User $user, Comment $comment): ?Dislike {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user_id')
            ->setParameter('user_id', $user->getId())
            ->andWhere('l.comment = :comment_id')
            ->setParameter('comment_id', $comment->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
