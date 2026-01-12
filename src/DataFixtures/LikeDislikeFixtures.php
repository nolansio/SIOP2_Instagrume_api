<?php

namespace App\DataFixtures;

use App\Entity\Like;
use App\Entity\Dislike;
use App\Entity\Publication;
use App\Entity\Comment;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LikeDislikeFixtures extends Fixture implements DependentFixtureInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $userRepository = $this->userRepository;

        $like1 = new Like();
        $like1->setUser($userRepository->findOneByUsername("albert"));
        $like1->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($like1);

        $like2 = new Like();
        $like2->setUser($userRepository->findOneByUsername("Jessica"));
        $like2->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($like2);

        $like3 = new Like();
        $like3->setUser($userRepository->findOneByUsername("loup"));
        $like3->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($like3);

        $like4 = new Like();
        $like4->setUser($userRepository->findOneByUsername("moderator"));
        $like4->setPublication($this->getReference('publication1', Publication::class));
        $manager->persist($like4);

        $like5 = new Like();
        $like5->setUser($userRepository->findOneByUsername("elon"));
        $like5->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($like5);

        $like6 = new Like();
        $like6->setUser($userRepository->findOneByUsername("Jessica"));
        $like6->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($like6);

        $like7 = new Like();
        $like7->setUser($userRepository->findOneByUsername("loup"));
        $like7->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($like7);

        $dislike1 = new Dislike();
        $dislike1->setUser($userRepository->findOneByUsername("moderator"));
        $dislike1->setPublication($this->getReference('publication2', Publication::class));
        $manager->persist($dislike1);

        $like8 = new Like();
        $like8->setUser($userRepository->findOneByUsername("albert"));
        $like8->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($like8);

        $like9 = new Like();
        $like9->setUser($userRepository->findOneByUsername("Jessica"));
        $like9->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($like9);

        $like10 = new Like();
        $like10->setUser($userRepository->findOneByUsername("loup"));
        $like10->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($like10);

        $like11 = new Like();
        $like11->setUser($userRepository->findOneByUsername("moderator"));
        $like11->setPublication($this->getReference('publication3', Publication::class));
        $manager->persist($like11);

        $like12 = new Like();
        $like12->setUser($userRepository->findOneByUsername("elon"));
        $like12->setPublication($this->getReference('publication4', Publication::class));
        $manager->persist($like12);

        $like13 = new Like();
        $like13->setUser($userRepository->findOneByUsername("albert"));
        $like13->setPublication($this->getReference('publication4', Publication::class));
        $manager->persist($like13);

        $like14 = new Like();
        $like14->setUser($userRepository->findOneByUsername("moderator"));
        $like14->setPublication($this->getReference('publication4', Publication::class));
        $manager->persist($like14);

        $dislike2 = new Dislike();
        $dislike2->setUser($userRepository->findOneByUsername("loup"));
        $dislike2->setPublication($this->getReference('publication4', Publication::class));
        $manager->persist($dislike2);

        $like15 = new Like();
        $like15->setUser($userRepository->findOneByUsername("elon"));
        $like15->setPublication($this->getReference('publication5', Publication::class));
        $manager->persist($like15);

        $like16 = new Like();
        $like16->setUser($userRepository->findOneByUsername("Jessica"));
        $like16->setPublication($this->getReference('publication5', Publication::class));
        $manager->persist($like16);

        $like17 = new Like();
        $like17->setUser($userRepository->findOneByUsername("loup"));
        $like17->setPublication($this->getReference('publication5', Publication::class));
        $manager->persist($like17);

        $like18 = new Like();
        $like18->setUser($userRepository->findOneByUsername("albert"));
        $like18->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($like18);

        $like19 = new Like();
        $like19->setUser($userRepository->findOneByUsername("moderator"));
        $like19->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($like19);

        $dislike3 = new Dislike();
        $dislike3->setUser($userRepository->findOneByUsername("elon"));
        $dislike3->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($dislike3);

        $dislike4 = new Dislike();
        $dislike4->setUser($userRepository->findOneByUsername("Jessica"));
        $dislike4->setPublication($this->getReference('publication6', Publication::class));
        $manager->persist($dislike4);

        $like20 = new Like();
        $like20->setUser($userRepository->findOneByUsername("elon"));
        $like20->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($like20);

        $like21 = new Like();
        $like21->setUser($userRepository->findOneByUsername("albert"));
        $like21->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($like21);

        $like22 = new Like();
        $like22->setUser($userRepository->findOneByUsername("Jessica"));
        $like22->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($like22);

        $like23 = new Like();
        $like23->setUser($userRepository->findOneByUsername("moderator"));
        $like23->setPublication($this->getReference('publication7', Publication::class));
        $manager->persist($like23);

        $likeComment1 = new Like();
        $likeComment1->setUser($userRepository->findOneByUsername("elon"));
        $likeComment1->setComment($this->getReference('comment1', Comment::class));
        $manager->persist($likeComment1);

        $likeComment2 = new Like();
        $likeComment2->setUser($userRepository->findOneByUsername("Jessica"));
        $likeComment2->setComment($this->getReference('comment1', Comment::class));
        $manager->persist($likeComment2);

        $likeComment3 = new Like();
        $likeComment3->setUser($userRepository->findOneByUsername("albert"));
        $likeComment3->setComment($this->getReference('comment3', Comment::class));
        $manager->persist($likeComment3);

        $likeComment4 = new Like();
        $likeComment4->setUser($userRepository->findOneByUsername("loup"));
        $likeComment4->setComment($this->getReference('comment3', Comment::class));
        $manager->persist($likeComment4);

        $likeComment5 = new Like();
        $likeComment5->setUser($userRepository->findOneByUsername("Jessica"));
        $likeComment5->setComment($this->getReference('comment4', Comment::class));
        $manager->persist($likeComment5);

        $likeComment6 = new Like();
        $likeComment6->setUser($userRepository->findOneByUsername("moderator"));
        $likeComment6->setComment($this->getReference('comment4', Comment::class));
        $manager->persist($likeComment6);

        $likeComment7 = new Like();
        $likeComment7->setUser($userRepository->findOneByUsername("elon"));
        $likeComment7->setComment($this->getReference('comment6', Comment::class));
        $manager->persist($likeComment7);

        $likeComment8 = new Like();
        $likeComment8->setUser($userRepository->findOneByUsername("Jessica"));
        $likeComment8->setComment($this->getReference('comment6', Comment::class));
        $manager->persist($likeComment8);

        $likeComment9 = new Like();
        $likeComment9->setUser($userRepository->findOneByUsername("loup"));
        $likeComment9->setComment($this->getReference('comment6', Comment::class));
        $manager->persist($likeComment9);

        $likeComment10 = new Like();
        $likeComment10->setUser($userRepository->findOneByUsername("elon"));
        $likeComment10->setComment($this->getReference('comment7', Comment::class));
        $manager->persist($likeComment10);

        $likeComment11 = new Like();
        $likeComment11->setUser($userRepository->findOneByUsername("albert"));
        $likeComment11->setComment($this->getReference('comment7', Comment::class));
        $manager->persist($likeComment11);

        $likeComment12 = new Like();
        $likeComment12->setUser($userRepository->findOneByUsername("Jessica"));
        $likeComment12->setComment($this->getReference('comment9', Comment::class));
        $manager->persist($likeComment12);

        $likeComment13 = new Like();
        $likeComment13->setUser($userRepository->findOneByUsername("loup"));
        $likeComment13->setComment($this->getReference('comment13', Comment::class));
        $manager->persist($likeComment13);

        $likeComment14 = new Like();
        $likeComment14->setUser($userRepository->findOneByUsername("moderator"));
        $likeComment14->setComment($this->getReference('comment13', Comment::class));
        $manager->persist($likeComment14);

        $dislikeComment1 = new Dislike();
        $dislikeComment1->setUser($userRepository->findOneByUsername("elon"));
        $dislikeComment1->setComment($this->getReference('comment13', Comment::class));
        $manager->persist($dislikeComment1);

        $likeComment15 = new Like();
        $likeComment15->setUser($userRepository->findOneByUsername("loup"));
        $likeComment15->setComment($this->getReference('comment16', Comment::class));
        $manager->persist($likeComment15);

        $likeComment16 = new Like();
        $likeComment16->setUser($userRepository->findOneByUsername("Jessica"));
        $likeComment16->setComment($this->getReference('comment16', Comment::class));
        $manager->persist($likeComment16);

        $likeComment17 = new Like();
        $likeComment17->setUser($userRepository->findOneByUsername("loup"));
        $likeComment17->setComment($this->getReference('comment17', Comment::class));
        $manager->persist($likeComment17);

        $likeComment18 = new Like();
        $likeComment18->setUser($userRepository->findOneByUsername("albert"));
        $likeComment18->setComment($this->getReference('comment17', Comment::class));
        $manager->persist($likeComment18);

        $likeComment19 = new Like();
        $likeComment19->setUser($userRepository->findOneByUsername("elon"));
        $likeComment19->setComment($this->getReference('comment17', Comment::class));
        $manager->persist($likeComment19);

        $likeComment20 = new Like();
        $likeComment20->setUser($userRepository->findOneByUsername("loup"));
        $likeComment20->setComment($this->getReference('comment18', Comment::class));
        $manager->persist($likeComment20);

        $likeComment21 = new Like();
        $likeComment21->setUser($userRepository->findOneByUsername("moderator"));
        $likeComment21->setComment($this->getReference('comment18', Comment::class));
        $manager->persist($likeComment21);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PublicationFixtures::class,
            CommentFixtures::class
        ];
    }
}
