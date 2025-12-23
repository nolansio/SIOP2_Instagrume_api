<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use App\Entity\Publication;
use Symfony\Component\Filesystem\Filesystem;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly ParameterBagInterface $params
    ) {
        parent::__construct($doctrine, Publication::class);
    }

    /**
     * Trouve toutes les publications avec leurs relations (optimisé pour éviter N+1)
     */
    public function findAllOptimized(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')->addSelect('u')
            ->leftJoin('p.images', 'i')->addSelect('i')
            ->leftJoin('p.likes', 'l')->addSelect('l')
            ->leftJoin('p.dislikes', 'd')->addSelect('d')
            ->leftJoin('p.comments', 'c')->addSelect('c')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Pagination optimisée des publications
     * 
     * @param int $page Numéro de page (commence à 1)
     * @param int $limit Nombre d'éléments par page
     * @return array ['data' => Publication[], 'total' => int, 'pages' => int, 'current_page' => int, 'per_page' => int]
     */
    public function findPaginated(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;

        // Requête pour les données avec toutes les relations chargées
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')->addSelect('u')
            ->leftJoin('p.images', 'i')->addSelect('i')
            ->leftJoin('p.likes', 'l')->addSelect('l')
            ->leftJoin('p.dislikes', 'd')->addSelect('d')
            ->leftJoin('p.comments', 'c')->addSelect('c')
            ->orderBy('p.created_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $publications = $query->getResult();

        // Requête pour le total (sans les relations pour être plus rapide)
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'data' => $publications,
            'total' => (int)$total,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
            'per_page' => $limit
        ];
    }

    /**
     * Trouve une publication par ID avec toutes ses relations chargées
     */
    public function findOneByIdOptimized(int $id): ?Publication
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')->addSelect('u')
            ->leftJoin('p.images', 'i')->addSelect('i')
            ->leftJoin('p.likes', 'l')->addSelect('l')
            ->leftJoin('p.dislikes', 'd')->addSelect('d')
            ->leftJoin('p.comments', 'c')->addSelect('c')
            ->leftJoin('c.user', 'cu')->addSelect('cu')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function create(?User $user, string $description, array $imagePaths): Publication
    {
        $publication = new Publication();

        $publication->setUser($user);
        $publication->setDescription($description);
        $publication->setCreatedAt(new DateTimeImmutable("now", new DateTimeZone("Europe/Paris")));

        foreach ($imagePaths as $path) {
            $image = new Image();
            $image->setDescription('Image');
            $image->setUrl($path);

            $publication->addImage($image);
        }

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($publication);
        $entityManager->flush();

        return $publication;
    }

    public function delete(Publication $publication): void
    {
        $filesystem = new Filesystem();
        $images = $publication->getImages();

        foreach ($images as $image) {
            $imagePath = $this->params->get('public_directory') . $image->getUrl();
            if ($filesystem->exists($imagePath)) {
                $filesystem->remove($imagePath);
            }
        }

        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($publication);
        $entityManager->flush();
    }

    public function update(Publication $publication, string $description): Publication
    {
        $entityManager = $this->doctrine->getManager();
        $publication->setDescription($description);
        $entityManager->flush();

        return $publication;
    }

    public function lock(Publication $publication): Publication
    {
        $entityManager = $this->doctrine->getManager();
        $publication->setLocked(true);
        $entityManager->flush();

        return $publication;
    }

    public function delock(Publication $publication): Publication
    {
        $entityManager = $this->doctrine->getManager();
        $publication->setLocked(false);
        $entityManager->flush();

        return $publication;
    }
}
