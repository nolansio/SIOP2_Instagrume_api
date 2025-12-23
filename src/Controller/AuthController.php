<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use function Symfony\Component\Clock\now;

class AuthController extends AbstractController
{

    public function __construct(
        private readonly JWTService $jwtService,
        private readonly UserRepository $userRepository
    ) {}

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
                        new OA\Property(property: 'error', type: 'string', example: "Invalid credentials")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User is banned'),
                    ]
                )
            )
        ]
    )]
    public function token(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!($username = $data['username'] ?? null) || !($password = $data['password'] ?? null)) {
            return $this->json(['error' => "Parameters 'username' and 'password' required"], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneByUsername($username);
        if (!$user || !$this->userRepository->isLoggable($user, $password)) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        if ($bannedResponse = $this->checkUserBan($user)) {
            return $bannedResponse;
        }

        $token = $this->jwtService->encodeToken([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ]);

        return $this->json(['token' => $token]);
    }

    private function checkUserBan($user): ?JsonResponse
    {
        if ($user->getBannedUntil() && $user->getBannedUntil() > now()) {
            $bannedUntil = $user->getBannedUntil()->format('d/m/Y à H:i');
            return $this->json([
                'error' => 'User is banned',
                'banned_until' => $bannedUntil,
                'message' => "Votre compte est banni jusqu'au {$bannedUntil}"
            ], Response::HTTP_FORBIDDEN);
        }
        return null;
    }
}
