<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository {

    private UserRepository $userRepository;

    public function __construct(ManagerRegistry $registry, UserRepository $userRepository) {
        parent::__construct($registry, Post::class);
        $this->userRepository = $userRepository;
    }

    public function findOneById($id): ?Post {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findMostRecentByPartDescription($description): ?Post {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.description) LIKE LOWER(:description)')
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults(1)
            ->setParameter('description', '%'.$description.'%')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllByPartDescription($description): array {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.description) LIKE LOWER(:description)')
            ->orderBy('p.created_at', 'DESC')
            ->setParameter('description', '%'. $description .'%')
            ->getQuery()
            ->getResult()
        ;
    }

}
