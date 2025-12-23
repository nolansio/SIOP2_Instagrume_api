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
class CommentRepository extends ServiceEntityRepository
{

    public function __construct(private readonly ManagerRegistry $doctrine)
    {
        parent::__construct($doctrine, Comment::class);
    }

    /**
     * Trouve tous les commentaires avec leurs relations (optimisé)
     */
    public function findAllOptimized(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')->addSelect('u')
            ->leftJoin('c.publication', 'p')->addSelect('p')
            ->leftJoin('c.likes', 'l')->addSelect('l')
            ->leftJoin('c.dislikes', 'd')->addSelect('d')
            ->leftJoin('c.comments', 'replies')->addSelect('replies')
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Pagination des commentaires
     * 
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @return array ['data' => Comment[], 'total' => int, 'pages' => int, 'current_page' => int, 'per_page' => int]
     */
    public function findPaginated(int $page = 1, int $limit = 50): array
    {
        $offset = ($page - 1) * $limit;

        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')->addSelect('u')
            ->leftJoin('c.publication', 'p')->addSelect('p')
            ->leftJoin('c.likes', 'l')->addSelect('l')
            ->leftJoin('c.dislikes', 'd')->addSelect('d')
            ->orderBy('c.created_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $comments = $query->getResult();

        $total = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'data' => $comments,
            'total' => (int)$total,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
            'per_page' => $limit
        ];
    }

    /**
     * Trouve un commentaire par ID avec toutes ses relations
     */
    public function findOneByIdOptimized(int $id): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')->addSelect('u')
            ->leftJoin('c.publication', 'p')->addSelect('p')
            ->leftJoin('c.likes', 'l')->addSelect('l')
            ->leftJoin('c.dislikes', 'd')->addSelect('d')
            ->leftJoin('c.comments', 'replies')->addSelect('replies')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function create(string $content, ?User $user, Publication $publication, ?Comment $original_comment): Comment
    {
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

    public function update(Comment $comment, string $content): Comment
    {
        $comment->setContent($content);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($comment);
        $entityManager->flush();
    }
}
