<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;

class UserController extends AbstractController {

    private UserRepository $userRepository;
    private JsonConverter $jsonConverter;

    public function __construct(UserRepository $userRepository, JsonConverter $jsonConverter) {
        $this->userRepository = $userRepository;
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/api/users', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'Récupère tous les utilisateurs',
        description: 'Récupération de tous les utilisateur',
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateurs récupérés avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Full authentication is required to access this resource.')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur interne du serveur',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Internal Server Error')
                    ]
                )
            )
        ]
    )]
    public function getAll(Request $request): Response {
        $users = $this->userRepository->findAll();

        return new Response($this->jsonConverter->encodeToJson($users), 200, ['Content-Type' => 'application/json']);
    }

}
