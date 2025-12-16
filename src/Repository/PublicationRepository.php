<?php

namespace App\Repository;

use App\Entity\Image;
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
class PublicationRepository extends ServiceEntityRepository {

    private ManagerRegistry $doctrine;
    private ParameterBagInterface $params;

    public function __construct(ManagerRegistry $registry, ManagerRegistry $doctrine, ParameterBagInterface $params) {
        parent::__construct($registry, Publication::class);
        $this->doctrine = $doctrine;
        $this->params = $params;
    }

    public function create($description, $user, $imagePaths): Publication {
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

    public function delete($publication): void {
        $entityManager = $this->doctrine->getManager();
        $filesystem = new Filesystem();
        $images = $publication->getImages();
        foreach ($images as $image) {
            $imagePath = $this->params->get('public_directory') . $image->getUrl();
            var_dump($imagePath);
            if ($filesystem->exists($imagePath)) {
                $filesystem->remove($imagePath);
            }
        }
        $entityManager->remove($publication);
        $entityManager->flush();
    }

    public function update($publication, $description): void {
        $entityManager = $this->doctrine->getManager();
        $publication->setDescription($description);
        $entityManager->flush();
    }

    public function updateIsLocked($publication, $value): void {
        $entityManager = $this->doctrine->getManager();
        $publication->setIsLocked($value);
        $entityManager->flush();
    }

}
