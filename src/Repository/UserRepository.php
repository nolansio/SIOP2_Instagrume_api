<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;

use App\Service\ImageService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface {

    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private ImageRepository $imageRepository;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, ImageRepository $imageRepository) {
        parent::__construct($doctrine, User::class);
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->imageRepository = $imageRepository;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function isLoggable(User $user, string $password): bool {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }


    public function findOneByUsername(string $username): ?User {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findUsernamesByUsername(string $username): array {
        $results = $this->createQueryBuilder('u')
            ->select('u.username')
            ->andWhere('u.username LIKE :username')
            ->setParameter('username', '%'.$username.'%')
            ->getQuery()
            ->getScalarResult()
        ;

        return array_column($results, 'username');
    }

    public function create(string $username, string $password): User {
        $user = new User();
        $user->setUsername($username);

        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function update(User $user, string $username, string $password): User {
        $user->setUsername($username);

        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function updateUsername(User $user, string $username): User {
        $user->setUsername($username);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function updatePassword(User $user, string $password): User {
        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function updateAvatar(User $user, UploadedFile $avatar): User {
        $path = '../public/images/'.uniqid().'.png';
        ImageService::compressAndResizeImage($avatar->getPathname(), $path, 800, 800, 75);

        $entityManager = $this->doctrine->getManager();
        $currentImg = $this->imageRepository->findBy(['user' => $user]);
        $newImg = new Image();

        if ($currentImg) {
            $currentImg = $currentImg[0];

            if (file_exists($currentImg->getUrl())) {
                unlink($currentImg->getUrl());
            }

            $entityManager->remove($currentImg);
        }

        $newImg->setUrl(str_replace('../public', '', $path));
        $newImg->setDescription($user->getUsername());
        $newImg->setUser($user);

        $entityManager->persist($newImg);
        $entityManager->flush();

        return $user;
    }

    public function delete(User $user): void {
        $entityManager = $this->doctrine->getManager();
        $images = $user->getImages();

        foreach ($images as $image) {
            $path = __DIR__ . '/../../public' . $image->getUrl();
            unlink($path);
        }

        $entityManager->remove($user);
        $entityManager->flush();
    }

    public function ban(User $user): User {
        $entityManager = $this->doctrine->getManager();
        $user->setBanned(true);
        $entityManager->flush();

        return $user;
    }

    public function deban(User $user): User {
        $entityManager = $this->doctrine->getManager();
        $user->setBanned(false);
        $entityManager->flush();

        return $user;
    }

}
