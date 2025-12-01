<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Publication;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface {

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void {
        $userRepository = $this->userRepository;

        // Publication 3
        $comment1 = new Comment();
        $comment1->setUser($userRepository->findOneByUsername("elon"));
        $comment1->setContent("J'aime bien la seconde image");
        $comment1->setCreatedAt(new DateTimeImmutable("-1 hour"));
        $comment1->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($comment1);

        $comment2 = new Comment();
        $comment2->setUser($userRepository->findOneByUsername("albert"));
        $comment2->setContent("Jolie photo !");
        $comment2->setCreatedAt(new DateTimeImmutable("-2 hour"));
        $comment2->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($comment2);

        $comment3 = new Comment();
        $comment3->setUser($userRepository->findOneByUsername("user"));
        $comment3->setContent("Merci !");
        $comment3->setCreatedAt(new DateTimeImmutable("now"));
        $comment3->setOriginalComment($comment1);
        $manager->persist($comment3);

        $manager->flush();
    }

    public function getDependencies(): array {
        return [PublicationFixtures::class];
    }

}
