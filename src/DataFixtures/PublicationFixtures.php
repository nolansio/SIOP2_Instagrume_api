<?php

namespace App\DataFixtures;

use App\Entity\Publication;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeZone;
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
        $publication1->setUser($this->userRepository->findOneByUsername("elon"));
        $publication1->setCreatedAt(new DateTimeImmutable("-16 day", new DateTimeZone("Europe/Paris")));
        $manager->persist($publication1);
        $this->addReference('publication1', $publication1);

        // Publication 2
        $publication2 = new Publication();
        $publication2->setDescription("Ce soir, c'est Saumon grillé aux légumes !");
        $publication2->setUser($userRepository->findOneByUsername("albert"));
        $publication2->setCreatedAt(new DateTimeImmutable("-7 day", new DateTimeZone("Europe/Paris")));
        $manager->persist($publication2);
        $this->addReference('publication2', $publication2);

        // Publication 3
        $publication3 = new Publication();
        $publication3->setDescription("Photo de cerise");
        $publication3->setUser($userRepository->findOneByUsername("elon"));
        $publication3->setCreatedAt(new DateTimeImmutable("-3 day", new DateTimeZone("Europe/Paris")));
        $manager->persist($publication3);
        $this->addReference('publication3', $publication3);

        // Publication 4
        $publication4 = new Publication();
        $publication4->setDescription("Trop hâte de gouter mon smoothie !");
        $publication4->setUser($userRepository->findOneByUsername("Jessica"));
        $publication4->setCreatedAt(new DateTimeImmutable("-41 day", new DateTimeZone("Europe/Paris")));
        $manager->persist($publication4);
        $this->addReference('publication4', $publication4);

        // Publication 5
        $publication5 = new Publication();
        $publication5->setDescription("C'est parti pour le jardinnage");
        $publication5->setUser($userRepository->findOneByUsername("albert"));
        $publication5->setCreatedAt(new DateTimeImmutable("-30 day", new DateTimeZone("Europe/Paris")));
        $manager->persist($publication5);
        $this->addReference('publication5', $publication5);

        // Publication 6
        $publication6 = new Publication();
        $publication6->setDescription("Cela ce mange ?");
        $publication6->setUser($userRepository->findOneByUsername("loup"));
        $publication6->setCreatedAt(new DateTimeImmutable("-24 day", new DateTimeZone("Europe/Paris")));
        $manager->persist($publication6);
        $this->addReference('publication6', $publication6);

        // Publication 7
        $publication7 = new Publication();
        $publication7->setDescription("J'ai fait ma première quiche aux légumes");
        $publication7->setUser($userRepository->findOneByUsername("loup"));
        $publication7->setCreatedAt(new DateTimeImmutable("-12 day", new DateTimeZone("Europe/Paris")));
        $manager->persist($publication7);
        $this->addReference('publication7', $publication7);

        $manager->flush();
    }

    public function getDependencies(): array {
        return [UserFixtures::class];
    }

}
