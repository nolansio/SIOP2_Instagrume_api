<?php

namespace App\Repository;

use App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher) {
        parent::__construct($doctrine, User::class);
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
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

    public function findOneByUsername($username): ?User {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findUsernamesByUsername($username): array {
        $results = $this->createQueryBuilder('u')
            ->select('u.username')
            ->andWhere('u.username LIKE :username')
            ->setParameter('username', '%'.$username.'%')
            ->getQuery()
            ->getScalarResult()
        ;

        return array_column($results, 'username');
    }

    public function create($username, $password): User {
        $user = new User();
        $user->setUsername($username);

        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);

        $entityManager = $this->doctrine->getManager();

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function update($username, $password, $user): User {
        $user->setUsername($username);
        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);
        // $this->upgradePassword($user, $password);
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return $user;
    }

    public function updateUsername($username, $user): User {
        $user->setUsername($username);
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return $user;
    }

    public function updatePassword($password, $user): User {
        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return $user;
    }

    public function delete($user): void {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
    }

    public function updateIsBan($user, $value): void {
        $entityManager = $this->doctrine->getManager();
        $user->setIsBanned($value);
        $entityManager->flush();
    }


}
