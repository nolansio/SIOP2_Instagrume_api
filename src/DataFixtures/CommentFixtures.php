<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Publication;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $userRepository = $this->userRepository;

        $comment1 = new Comment();
        $comment1->setUser($userRepository->findOneByUsername("albert"));
        $comment1->setContent("Super ! C'est génial de cultiver ses propres légumes");
        $comment1->setCreatedAt(new DateTimeImmutable("-15 day", new DateTimeZone("Europe/Paris")));
        $comment1->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($comment1);
        $this->addReference('comment1', $comment1);

        $comment1_reply1 = new Comment();
        $comment1_reply1->setUser($userRepository->findOneByUsername("elon"));
        $comment1_reply1->setContent("Merci ! C'est vraiment satisfaisant");
        $comment1_reply1->setCreatedAt(new DateTimeImmutable("-15 day -2 hours", new DateTimeZone("Europe/Paris")));
        $comment1_reply1->setOriginalComment($comment1);
        $manager->persist($comment1_reply1);

        $comment2 = new Comment();
        $comment2->setUser($userRepository->findOneByUsername("Jessica"));
        $comment2->setContent("J'aimerais bien avoir un jardin aussi");
        $comment2->setCreatedAt(new DateTimeImmutable("-14 day", new DateTimeZone("Europe/Paris")));
        $comment2->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($comment2);
        $this->addReference('comment2', $comment2);

        $comment3 = new Comment();
        $comment3->setUser($userRepository->findOneByUsername("elon"));
        $comment3->setContent("Ça a l'air délicieux !");
        $comment3->setCreatedAt(new DateTimeImmutable("-6 day", new DateTimeZone("Europe/Paris")));
        $comment3->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($comment3);
        $this->addReference('comment3', $comment3);

        $comment4 = new Comment();
        $comment4->setUser($userRepository->findOneByUsername("loup"));
        $comment4->setContent("Tu peux partager la recette ?");
        $comment4->setCreatedAt(new DateTimeImmutable("-6 day -3 hours", new DateTimeZone("Europe/Paris")));
        $comment4->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($comment4);
        $this->addReference('comment4', $comment4);

        $comment4_reply1 = new Comment();
        $comment4_reply1->setUser($userRepository->findOneByUsername("albert"));
        $comment4_reply1->setContent("Bien sûr ! Je vais la poster bientôt");
        $comment4_reply1->setCreatedAt(new DateTimeImmutable("-6 day -4 hours", new DateTimeZone("Europe/Paris")));
        $comment4_reply1->setOriginalComment($comment4);
        $manager->persist($comment4_reply1);

        $comment5 = new Comment();
        $comment5->setUser($userRepository->findOneByUsername("moderator"));
        $comment5->setContent("Belle présentation !");
        $comment5->setCreatedAt(new DateTimeImmutable("-5 day", new DateTimeZone("Europe/Paris")));
        $comment5->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($comment5);
        $this->addReference('comment5', $comment5);

        $comment6 = new Comment();
        $comment6->setUser($userRepository->findOneByUsername("albert"));
        $comment6->setContent("J'aime bien la seconde image");
        $comment6->setCreatedAt(new DateTimeImmutable("-2 day", new DateTimeZone("Europe/Paris")));
        $comment6->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($comment6);
        $this->addReference('comment6', $comment6);

        $comment6_reply1 = new Comment();
        $comment6_reply1->setUser($userRepository->findOneByUsername("elon"));
        $comment6_reply1->setContent("Merci ! Les cerises sont vraiment belles cette année");
        $comment6_reply1->setCreatedAt(new DateTimeImmutable("-2 day -1 hour", new DateTimeZone("Europe/Paris")));
        $comment6_reply1->setOriginalComment($comment6);
        $manager->persist($comment6_reply1);

        $comment7 = new Comment();
        $comment7->setUser($userRepository->findOneByUsername("Jessica"));
        $comment7->setContent("Elles ont l'air juteuses 🍒");
        $comment7->setCreatedAt(new DateTimeImmutable("-2 day -5 hours", new DateTimeZone("Europe/Paris")));
        $comment7->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($comment7);
        $this->addReference('comment7', $comment7);

        $comment8 = new Comment();
        $comment8->setUser($userRepository->findOneByUsername("loup"));
        $comment8->setContent("Tu as de la chance d'avoir un cerisier !");
        $comment8->setCreatedAt(new DateTimeImmutable("-1 day", new DateTimeZone("Europe/Paris")));
        $comment8->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($comment8);
        $this->addReference('comment8', $comment8);

        $comment9 = new Comment();
        $comment9->setUser($userRepository->findOneByUsername("elon"));
        $comment9->setContent("Quelle couleur ! Qu'est-ce qu'il y a dedans ?");
        $comment9->setCreatedAt(new DateTimeImmutable("-40 day", new DateTimeZone("Europe/Paris")));
        $comment9->setPublication($this->getReference('publication4', Publication::class));
        $manager->persist($comment9);
        $this->addReference('comment9', $comment9);

        $comment9_reply1 = new Comment();
        $comment9_reply1->setUser($userRepository->findOneByUsername("Jessica"));
        $comment9_reply1->setContent("Fraise, banane et un peu de lait d'amande !");
        $comment9_reply1->setCreatedAt(new DateTimeImmutable("-40 day -2 hours", new DateTimeZone("Europe/Paris")));
        $comment9_reply1->setOriginalComment($comment9);
        $manager->persist($comment9_reply1);

        $comment9_reply2 = new Comment();
        $comment9_reply2->setUser($userRepository->findOneByUsername("albert"));
        $comment9_reply2->setContent("Excellente combinaison !");
        $comment9_reply2->setCreatedAt(new DateTimeImmutable("-39 day", new DateTimeZone("Europe/Paris")));
        $comment9_reply2->setOriginalComment($comment9);
        $manager->persist($comment9_reply2);

        $comment10 = new Comment();
        $comment10->setUser($userRepository->findOneByUsername("moderator"));
        $comment10->setContent("Bon pour la santé 💪");
        $comment10->setCreatedAt(new DateTimeImmutable("-38 day", new DateTimeZone("Europe/Paris")));
        $comment10->setPublication($this->getReference('publication4', Publication::class));
        $manager->persist($comment10);
        $this->addReference('comment10', $comment10);

        $comment11 = new Comment();
        $comment11->setUser($userRepository->findOneByUsername("loup"));
        $comment11->setContent("Bon courage pour le jardinage !");
        $comment11->setCreatedAt(new DateTimeImmutable("-29 day", new DateTimeZone("Europe/Paris")));
        $comment11->setPublication($this->getReference('publication5', Publication::class));
        $manager->persist($comment11);
        $this->addReference('comment11', $comment11);

        $comment12 = new Comment();
        $comment12->setUser($userRepository->findOneByUsername("elon"));
        $comment12->setContent("Tu vas planter quoi ?");
        $comment12->setCreatedAt(new DateTimeImmutable("-28 day", new DateTimeZone("Europe/Paris")));
        $comment12->setPublication($this->getReference('publication5', Publication::class));
        $manager->persist($comment12);
        $this->addReference('comment12', $comment12);

        $comment12_reply1 = new Comment();
        $comment12_reply1->setUser($userRepository->findOneByUsername("albert"));
        $comment12_reply1->setContent("Des tomates, courgettes et quelques herbes aromatiques");
        $comment12_reply1->setCreatedAt(new DateTimeImmutable("-28 day -3 hours", new DateTimeZone("Europe/Paris")));
        $comment12_reply1->setOriginalComment($comment12);
        $manager->persist($comment12_reply1);

        $comment13 = new Comment();
        $comment13->setUser($userRepository->findOneByUsername("Jessica"));
        $comment13->setContent("Oui, mais fais attention à ce que rien ne soit pourri");
        $comment13->setCreatedAt(new DateTimeImmutable("-23 day", new DateTimeZone("Europe/Paris")));
        $comment13->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($comment13);
        $this->addReference('comment13', $comment13);

        $comment14 = new Comment();
        $comment14->setUser($userRepository->findOneByUsername("moderator"));
        $comment14->setContent("C'est quoi exactement ?");
        $comment14->setCreatedAt(new DateTimeImmutable("-22 day", new DateTimeZone("Europe/Paris")));
        $comment14->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($comment14);
        $this->addReference('comment14', $comment14);

        $comment14_reply1 = new Comment();
        $comment14_reply1->setUser($userRepository->findOneByUsername("loup"));
        $comment14_reply1->setContent("Je pense que c'est du topinambour");
        $comment14_reply1->setCreatedAt(new DateTimeImmutable("-22 day -4 hours", new DateTimeZone("Europe/Paris")));
        $comment14_reply1->setOriginalComment($comment14);
        $manager->persist($comment14_reply1);

        $comment15 = new Comment();
        $comment15->setUser($userRepository->findOneByUsername("albert"));
        $comment15->setContent("Goûte d'abord une petite quantité pour voir");
        $comment15->setCreatedAt(new DateTimeImmutable("-21 day", new DateTimeZone("Europe/Paris")));
        $comment15->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($comment15);
        $this->addReference('comment15', $comment15);

        $comment16 = new Comment();
        $comment16->setUser($userRepository->findOneByUsername("elon"));
        $comment16->setContent("Elle a l'air super bonne !");
        $comment16->setCreatedAt(new DateTimeImmutable("-11 day", new DateTimeZone("Europe/Paris")));
        $comment16->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($comment16);
        $this->addReference('comment16', $comment16);

        $comment17 = new Comment();
        $comment17->setUser($userRepository->findOneByUsername("Jessica"));
        $comment17->setContent("Bravo pour ta première quiche !");
        $comment17->setCreatedAt(new DateTimeImmutable("-11 day -2 hours", new DateTimeZone("Europe/Paris")));
        $comment17->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($comment17);
        $this->addReference('comment17', $comment17);

        $comment17_reply1 = new Comment();
        $comment17_reply1->setUser($userRepository->findOneByUsername("loup"));
        $comment17_reply1->setContent("Merci ! J'en suis assez fier 😊");
        $comment17_reply1->setCreatedAt(new DateTimeImmutable("-10 day", new DateTimeZone("Europe/Paris")));
        $comment17_reply1->setOriginalComment($comment17);
        $manager->persist($comment17_reply1);

        $comment18 = new Comment();
        $comment18->setUser($userRepository->findOneByUsername("albert"));
        $comment18->setContent("La cuisson a l'air parfaite");
        $comment18->setCreatedAt(new DateTimeImmutable("-10 day -5 hours", new DateTimeZone("Europe/Paris")));
        $comment18->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($comment18);
        $this->addReference('comment18', $comment18);

        $comment19 = new Comment();
        $comment19->setUser($userRepository->findOneByUsername("moderator"));
        $comment19->setContent("Tu as utilisé quels légumes ?");
        $comment19->setCreatedAt(new DateTimeImmutable("-9 day", new DateTimeZone("Europe/Paris")));
        $comment19->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($comment19);
        $this->addReference('comment19', $comment19);

        $comment19_reply1 = new Comment();
        $comment19_reply1->setUser($userRepository->findOneByUsername("loup"));
        $comment19_reply1->setContent("Courgettes, poivrons et oignons principalement");
        $comment19_reply1->setCreatedAt(new DateTimeImmutable("-9 day -1 hour", new DateTimeZone("Europe/Paris")));
        $comment19_reply1->setOriginalComment($comment19);
        $manager->persist($comment19_reply1);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [PublicationFixtures::class];
    }
}
