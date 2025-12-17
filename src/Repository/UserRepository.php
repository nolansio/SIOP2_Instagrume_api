<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use App\Service\ImageService;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface {

    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private ImageRepository $imageRepository;
    private ParameterBagInterface $params;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, ImageRepository $imageRepository, ParameterBagInterface $params) {
        parent::__construct($doctrine, User::class);
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->imageRepository = $imageRepository;
        $this->params = $params;
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

        ImageService::compressAndResizeImage(
            $avatar->getPathname(),
            $path, 800,
            800,
            75
        );

        $entityManager = $this->doctrine->getManager();
        $avatars = $this->imageRepository->findBy(['user' => $user]);

        if ($avatars) {
            foreach ($avatars as $image) {
                @unlink('../public/'.$image->getUrl());
                $entityManager->remove($image);
            }
        }

        $newAvatar = new Image();
        $newAvatar->setUrl(str_replace('../public', '', $path));
        $newAvatar->setDescription($user->getUsername());
        $newAvatar->setUser($user);

        $entityManager->persist($newAvatar);
        $entityManager->flush();

        return $user;
    }

    public function delete(User $user): void {
        $entityManager = $this->doctrine->getManager();
        $filesystem = new Filesystem();
        $images = $user->getImages();
        foreach ($images as $image) {
            $imagePath = $this->params->get('public_directory') . $image->getUrl();
            if ($filesystem->exists($imagePath)) {
                $filesystem->remove($imagePath);
            }
        }
        $entityManager->remove($user);
        $entityManager->flush();
    }

    public function updateBannedUntil($user, $value): User {
        $entityManager = $this->doctrine->getManager();
        $user->setBannedUntil($value);
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
