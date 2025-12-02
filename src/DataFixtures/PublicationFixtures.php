<?php

namespace App\DataFixtures;

use App\Entity\Publication;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PublicationFixtures extends Fixture implements DependentFixtureInterface {

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void {
        $userRepository = $this->userRepository;

        // Publication 1
        $publication1 = new Publication();
        $publication1->setDescription("Je cultive moi-même mes légumes");
        $publication1->setUser($this->userRepository->findOneByUsername("user"));
        $publication1->setCreatedAt(new DateTimeImmutable("-2 day"));
        $manager->persist($publication1);
        $this->addReference('publication1', $publication1);

        // Publication 2
        $publication2 = new Publication();
        $publication2->setDescription("Ce soir, c'est Saumon grillé aux légumes !");
        $publication2->setUser($userRepository->findOneByUsername("albert"));
        $publication2->setCreatedAt(new DateTimeImmutable("-1 day"));
        $manager->persist($publication2);
        $this->addReference('publication2', $publication2);

        // Publication 3
        $publication3 = new Publication();
        $publication3->setDescription("Photo de cerise");
        $publication3->setUser($userRepository->findOneByUsername("user"));
        $publication3->setCreatedAt(new DateTimeImmutable("-1 day"));
        $manager->persist($publication3);
        $this->addReference('publication3', $publication3);

        $manager->flush();
    }

    public function getDependencies(): array {
        return [UserFixtures::class];
    }

}
