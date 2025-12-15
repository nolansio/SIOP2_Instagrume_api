<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;

class AuthController extends AbstractController {

    #[Route('/api/token', methods: ['POST'])]
    #[OA\Post(
        path: '/api/token',
        summary: "Générer un token",
        description: "Authentification d'un utilisateur et renvoie d'un jeton",
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'user'),
                    new OA\Property(property: 'password', type: 'string', example: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Jeton généré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'username' and 'password' required")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Identifiants invalides',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid credentials')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Refusé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User is banned')
                    ]
                )
            )
        ]
    )]
    public function token(Request $request, JWTService $jwtService, ManagerRegistry $doctrine): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            return new JsonResponse(['error' => "Parameters 'username' and 'password' required"], 400);
        }

        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($user->getIsBanned()) {
            return new JsonResponse(['error'=> 'User is banned'], 403);
        }
        $jwt = $jwtService->encodeToken([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ]);
        return new JsonResponse(['token' => $jwt], 200);
    }

}
