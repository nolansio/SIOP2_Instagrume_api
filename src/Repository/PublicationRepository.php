<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\Publication;
use App\Entity\User;
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

    public function __construct(ManagerRegistry $doctrine, ParameterBagInterface $params) {
        parent::__construct($doctrine, Publication::class);
        $this->doctrine = $doctrine;
        $this->params = $params;
    }

    public function create(?User $user, string $description, array $imagePaths): Publication {
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

    public function delete(Publication $publication): void {
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

    public function update(Publication $publication, string $description): Publication {
        $entityManager = $this->doctrine->getManager();
        $publication->setDescription($description);
        $entityManager->flush();

        return $publication;
    }

    public function lock(Publication $publication): Publication {
        $entityManager = $this->doctrine->getManager();
        $publication->setLocked(true);
        $entityManager->flush();

        return $publication;
    }

    public function delock(Publication $publication): Publication {
        $entityManager = $this->doctrine->getManager();
        $publication->setLocked(false);
        $entityManager->flush();

        return $publication;
    }

}
