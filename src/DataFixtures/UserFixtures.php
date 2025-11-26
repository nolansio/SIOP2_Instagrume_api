<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture {

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void {
        $user1 = new User();
        $user1->setUsername('user');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'user'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('admin');
        $user2->setRoles(["ROLE_ADMIN"]);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'admin'));
        $manager->persist($user2);

        $user3 = new User();
        $user3->setUsername('root');
        $user3->setRoles(["ROLE_ADMIN"]);
        $user3->setPassword($this->passwordHasher->hashPassword($user3, 'root'));
        $manager->persist($user3);

        $user4 = new User();
        $user4->setUsername('albert');
        $user4->setPassword($this->passwordHasher->hashPassword($user4, 'albert'));
        $manager->persist($user4);

        $user5 = new User();
        $user5->setUsername('elon');
        $user5->setPassword($this->passwordHasher->hashPassword($user5, 'elon'));
        $manager->persist($user5);

        $manager->flush();
    }

}
