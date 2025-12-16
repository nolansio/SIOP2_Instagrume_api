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
            $image->setDescription('Image');
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
        $images = $publication->getImages();

        foreach ($images as $image) {
            $path = __DIR__ . '/../../public' . $image->getUrl();
            unlink($path);
        }

        $entityManager->remove($publication);
        $entityManager->flush();
    }

    public function update($publication, $description): void {
        $entityManager = $this->doctrine->getManager();
        $publication->setDescription($description);
        $entityManager->flush();
    }

    public function lock($publication): void {
        $entityManager = $this->doctrine->getManager();
        $publication->setIsLocked(true);
        $entityManager->flush();
    }

    public function delock(Publication $publication): void {
        $entityManager = $this->doctrine->getManager();
        $publication->setLocked(false);
        $entityManager->flush();
    }

}
