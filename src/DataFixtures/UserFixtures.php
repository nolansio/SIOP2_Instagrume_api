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
        $user1->setUsername('root');
        $user1->setRoles(["ROLE_ADMIN"]);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'root'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('moderator');
        $user2->setRoles(["ROLE_MOD"]);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'moderator'));
        $manager->persist($user2);

        $user3 = new User();
        $user3->setUsername('albert');
        $user3->setPassword($this->passwordHasher->hashPassword($user3, 'albert'));
        $manager->persist($user3);

        $user4 = new User();
        $user4->setUsername('elon');
        $user4->setPassword($this->passwordHasher->hashPassword($user4, 'elon'));
        $manager->persist($user4);

        $user5 = new User();
        $user5->setUsername('Jessica');
        $user5->setPassword($this->passwordHasher->hashPassword($user5, 'Jessica'));
        $manager->persist($user5);

        $user6 = new User();
        $user6->setUsername('loup');
        $user6->setPassword($this->passwordHasher->hashPassword($user6, 'loup'));
        $manager->persist($user6);

        $manager->flush();
    }

}
