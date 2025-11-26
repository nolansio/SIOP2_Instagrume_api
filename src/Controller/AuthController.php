<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends AbstractController {

    #[Route('/api/token', methods: ['POST'])]
    public function token(Request $request, JWTService $jwtService, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }
        $jwt = $jwtService->encodeToken([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
        return new JsonResponse(['token' => $jwt]);
    }

}
