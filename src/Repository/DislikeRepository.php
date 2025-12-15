<?php

namespace App\Repository;

use App\Entity\Dislike;
use App\Entity\Comment;
use App\Entity\Publication;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dislike>
 */
class DislikeRepository extends ServiceEntityRepository
{
    public function __construct(private ManagerRegistry $doctrine)
    {
        parent::__construct($doctrine, Dislike::class);
    }

    public function create($like): Dislike {
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($like);
        $entityManager->flush();
        return $like;
    }

    public function delete($like): Dislike {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($like);
        $entityManager->flush();
        return $like;
    }

    public function findDislikeByUserAndPublication(User $user, Publication $publication): ?Dislike {
        return $this->createQueryBuilder('l')
        ->andWhere('l.user = :user_id')
        ->setParameter('user_id', $user->getId())
        ->andWhere('l.publication = :publication_id')
        ->setParameter('publication_id', $publication->getId())
        ->getQuery()
        ->getOneOrNullResult();
    }


    public function findDislikeByUserAndComment(User $user, Comment $comment): ?Dislike {
        return $this->createQueryBuilder('l')
        ->andWhere('l.user = :user_id')
        ->setParameter('user_id', $user->getId())
        ->andWhere('l.comment = :comment_id')
        ->setParameter('comment_id', $comment->getId())
        ->getQuery()
        ->getOneOrNullResult();
    }

    //    /**
    //     * @return Dislike[] Returns an array of Dislike objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Dislike
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
