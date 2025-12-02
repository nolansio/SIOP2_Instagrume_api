<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\Publication;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository {

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $registry, ManagerRegistry $doctrine) {
        parent::__construct($registry, Publication::class);
        $this->doctrine = $doctrine;
    }

    public function create($description, $user, $imagePaths): Publication {
        $publication = new Publication();

        $publication->setUser($user);
        $publication->setDescription($description);
        $publication->setCreatedAt(new DateTimeImmutable("now"));

        foreach ($imagePaths as $path) {
            $image = new Image();
            $image->setDescription('Image'); // TODO
            $image->setUrl($path);

            $publication->addImage($image);
        }

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($publication);
        $entityManager->flush();

        return $publication;
    }

    public function delete($publication): void {
        $entityManager = $this->doctrine->getManager();

        $entityManager->remove($publication);
        $entityManager->flush();
    }

}
