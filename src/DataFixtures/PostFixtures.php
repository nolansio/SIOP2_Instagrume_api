<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends Fixture implements DependentFixtureInterface {

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void {
        $userRepository = $this->userRepository;

        // Publication 1
        $post1 = new Post();
        $post1->setDescription("Je cultive moi-même mes légumes");
        $post1->setUser($this->userRepository->findOneByUsername("user"));
        $post1->setCreatedAt(new DateTimeImmutable("-2 day"));
        $manager->persist($post1);
        $this->addReference('post1', $post1);

        // Publication 2
        $post2 = new Post();
        $post2->setDescription("Ce soir, c'est Saumon grillé aux légumes !");
        $post2->setUser($userRepository->findOneByUsername("albert"));
        $post2->setCreatedAt(new DateTimeImmutable("-1 day"));
        $manager->persist($post2);
        $this->addReference('post2', $post2);

        // Publication 3
        $post3 = new Post();
        $post3->setDescription("Photo de cerise");
        $post3->setUser($userRepository->findOneByUsername("user"));
        $post3->setCreatedAt(new DateTimeImmutable("-1 day"));
        $manager->persist($post3);
        $this->addReference('post3', $post3);

        $manager->flush();
    }

    public function getDependencies(): array {
        return [UserFixtures::class];
    }

}
