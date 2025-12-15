<?php

namespace App\Repository;

use App\Entity\Like;
use App\Entity\User;
use App\Entity\Publication;
use App\Entity\Comment;
use Cassandra\Tuple;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository {

    public function __construct(private ManagerRegistry $doctrine) {
        parent::__construct($doctrine, Like::class);
    }

    public function create(?User $user, ?Publication $publication, ?Comment $comment): Like {
        $entityManager = $this->doctrine->getManager();

        $like = new Like();
        $like->setUser($user);
        $like->setPublication($publication);
        $like->setComment($comment);

        $entityManager->persist($like);
        $entityManager->flush();
        return $like;
    }

    public function delete($like): Like {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($like);
        $entityManager->flush();
        return $like;
    }

    public function findLikeByUserAndPublication(?User $user, Publication $publication): ?Like {
        return $this->createQueryBuilder('l')
        ->andWhere('l.user = :user_id')
        ->setParameter('user_id', $user->getId())
        ->andWhere('l.publication = :publication_id')
        ->setParameter('publication_id', $publication->getId())
        ->getQuery()
        ->getOneOrNullResult();
    }


    public function findLikeByUserAndComment(?User $user, Comment $comment): ?Like {
        return $this->createQueryBuilder('l')
        ->andWhere('l.user = :user_id')
        ->setParameter('user_id', $user->getId())
        ->andWhere('l.comment = :comment_id')
        ->setParameter('comment_id', $comment->getId())
        ->getQuery()
        ->getOneOrNullResult();
    }
//    /**
//     * @return Like[] Returns an array of Like objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Like
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
