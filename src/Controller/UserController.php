<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
                        new OA\Property(property: 'id', type: 'int', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'user'),
                        new OA\Property(property: 'user_identifier', type: 'string', example: 'user'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'password', type: 'string', example: '$2y$13$IZVb2Y5dGZmk...'),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'posts', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: [])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Missing token / Invalid token')
                    ]
                )
            )
        ]
    )]
    public function getAll(): Response {
        $users = $this->userRepository->findAll();

        return new Response($this->jsonConverter->encodeToJson($users), 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/api/users/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Récupère un utilisateur par son ID',
        description: "Récupération d'un utilisateur par son ID",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'int', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'user'),
                        new OA\Property(property: 'user_identifier', type: 'string', example: 'user'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'password', type: 'string', example: '$2y$13$IZVb2Y5dGZmk...'),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'posts', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: [])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Missing token / Invalid token')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur non trouvé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function getById($id): JsonResponse {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse($this->jsonConverter->encodeToJson($user), 200, [], true);
    }

    #[Route('/api/users/username/{username}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/username/{username}',
        summary: "Récupère un utilisateur par son nom nom d'utilisateur",
        description: "Récupération d'un utilisateur par son nom d'utilisateur",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'int', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'user'),
                        new OA\Property(property: 'user_identifier', type: 'string', example: 'user'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'password', type: 'string', example: '$2y$13$IZVb2Y5dGZmk...'),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'posts', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: [])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Missing token / Invalid token')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur non trouvé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function getByUsername($username): JsonResponse {
        $user = $this->userRepository->findOneByUsername($username);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse($this->jsonConverter->encodeToJson($user), 200, [], true);
    }

}
