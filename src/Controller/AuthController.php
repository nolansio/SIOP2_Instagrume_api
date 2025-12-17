<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use function Symfony\Component\Clock\now;

class AuthController extends AbstractController {

    private JWTService $jwtService;
    private UserRepository $userRepository;

    public function __construct(JWTService $jwtService, UserRepository $userRepository) {
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
    }

    #[Route('/api/token', methods: ['POST'])]
    #[OA\Post(
        path: '/api/token',
        summary: "Générer un jeton d'authentification",
        description: "Génération d'un jeton d'authentification",
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
                description: "Jeton d'authentification généré avec succès",
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
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Incorrect password")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User is banned until : {Date}')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function token(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            return new JsonResponse(['error' => "Parameters 'username' and 'password' required"], 400);
        }

        $user = $this->userRepository->findOneByUsername($username);
        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);
        }

        if (!$this->userRepository->isLoggable($user, $password)) {
            return new JsonResponse(['error' => "Incorrect password"], 401);
        }

        if ($user->getBannedUntil() > now()) {
            return new JsonResponse(['error'=> 'User is banned until : ' . $user->getBannedUntil()->format('Y-m-d H:i:s')], 403);
        }

        $token = $this->jwtService->encodeToken([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ]);

        return new JsonResponse(['token' => $token], 200);
    }

}
