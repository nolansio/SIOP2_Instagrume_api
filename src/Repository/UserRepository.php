<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use App\Service\ImageService;
use DateTime;
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
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($doctrine, User::class);
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function isLoggable(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function findOneByUsername(string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUsernamesByUsername(string $username): array
    {
        $results = $this->createQueryBuilder('u')
            ->select('u.username')
            ->andWhere('u.username LIKE :username')
            ->setParameter('username', '%' . $username . '%')
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'username');
    }

    /**
     * Trouve tous les utilisateurs avec pagination
     * 
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @return array ['data' => User[], 'total' => int, 'pages' => int, 'current_page' => int, 'per_page' => int]
     */
    public function findPaginated(int $page = 1, int $limit = 50): array
    {
        $offset = ($page - 1) * $limit;

        $query = $this->createQueryBuilder('u')
            ->leftJoin('u.publications', 'p')->addSelect('p')
            ->leftJoin('u.images', 'i')->addSelect('i')
            ->orderBy('u.username', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $users = $query->getResult();

        $total = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'data' => $users,
            'total' => (int)$total,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
            'per_page' => $limit
        ];
    }

    /**
     * Trouve un utilisateur par ID avec ses relations chargées
     */
    public function findOneByIdOptimized(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.publications', 'p')->addSelect('p')
            ->leftJoin('u.images', 'i')->addSelect('i')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function create(string $username, string $password): User
    {
        $user = new User();
        $user->setUsername($username);

        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function update(User $user, string $username, string $password): User
    {
        $user->setUsername($username);

        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function updateUsername(User $user, string $username): User
    {
        $user->setUsername($username);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function updatePassword(User $user, string $password): User
    {
        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function updateAvatar(User $user, UploadedFile $avatar): User
    {
        $path = '../public/images/' . uniqid() . '.png';

        ImageService::compressAndResizeImage(
            $avatar->getPathname(),
            $path,
            800,
            800,
            75
        );

        $entityManager = $this->doctrine->getManager();
        $images = $user->getImages();

        foreach ($images as $image) {
            @unlink('../public/' . $image->getUrl());
            $entityManager->remove($image);
        }

        $newAvatar = new Image();
        $newAvatar->setUrl(str_replace('../public', '', $path));
        $newAvatar->setDescription($user->getUsername());
        $user->addImage($newAvatar);

        $entityManager->persist($newAvatar);
        $entityManager->flush();

        return $user;
    }

    public function delete(User $user): void
    {
        $entityManager = $this->doctrine->getManager();
        $images = $user->getImages();

        foreach ($images as $image) {
            @unlink('../public/' . $image->getUrl());
            $entityManager->remove($image);
        }

        $entityManager->remove($user);
        $entityManager->flush();
    }

    public function updateBannedUntil(User $user, DateTime $date): User
    {
        $entityManager = $this->doctrine->getManager();
        $user->setBannedUntil($date);
        $entityManager->flush();

        return $user;
    }
}
