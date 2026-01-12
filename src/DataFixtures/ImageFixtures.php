<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Publication;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture implements DependentFixtureInterface {

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void {
        $userRepository = $this->userRepository;

        // Publication 1
        $image1 = new Image();
        $image1->setDescription("Carottes");
        $image1->setUrl("/images/upload1.png");
        $image1->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($image1);

        $image2 = new Image();
        $image2->setDescription("Choux");
        $image2->setUrl("/images/upload2.jpg");
        $image2->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($image2);

        $image3 = new Image();
        $image3->setDescription("Patates");
        $image3->setUrl("/images/upload3.webp");
        $image3->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($image3);

        // Publication 2
        $image4 = new Image();
        $image4->setDescription("Saumon grillé aux légumes");
        $image4->setUrl("/images/upload4.png");
        $image4->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($image4);

        // Publication 3
        $image5 = new Image();
        $image5->setDescription("Cerise 1");
        $image5->setUrl("/images/upload5.jpg");
        $image5->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($image5);

        $image6 = new Image();
        $image6->setDescription("Cerise 2");
        $image6->setUrl("/images/upload6.jpg");
        $image6->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($image6);

        // Publication 3
        $image9 = new Image();
        $image9->setDescription("Smoothie");
        $image9->setUrl("/images/upload9.jpg");
        $image9->setPublication($this->getReference('publication4', Publication::class));
        $manager->persist($image9);

        // Publication 4
        $image10 = new Image();
        $image10->setDescription("Jardinnage");
        $image10->setUrl("/images/upload10.jpg");
        $image10->setPublication($this->getReference('publication5', Publication::class));
        $manager->persist($image10);

        // Publication 5
        $image11 = new Image();
        $image11->setDescription("Grosse citrouille");
        $image11->setUrl("/images/upload11.jpg");
        $image11->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($image11);

        // Publication 6
        $image12 = new Image();
        $image12->setDescription("Guiche au légumes");
        $image12->setUrl("/images/upload12.jpg");
        $image12->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($image12);

        // Avatars
        $image7 = new Image();
        $image7->setDescription("albert");
        $image7->setUrl("/images/upload7.jpg");
        $image7->setUser($userRepository->findOneByUsername("albert"));
        $manager->persist($image7);

        $image8 = new Image();
        $image8->setDescription("elon");
        $image8->setUrl("/images/upload8.jpg");
        $image8->setUser($userRepository->findOneByUsername("elon"));
        $manager->persist($image8);

        $image13 = new Image();
        $image13->setDescription("loup");
        $image13->setUrl("/images/upload13.jpg");
        $image13->setUser($userRepository->findOneByUsername("loup"));
        $manager->persist($image13);

        $image14 = new Image();
        $image14->setDescription("Jessica");
        $image14->setUrl("/images/upload14.jpg");
        $image14->setUser($userRepository->findOneByUsername("Jessica"));
        $manager->persist($image14);

        $manager->flush();
    }

    public function getDependencies(): array {
        return [PublicationFixtures::class];
    }

}
