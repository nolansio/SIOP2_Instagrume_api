<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        summary: "Récupère tous les utilisateurs ou un utilisateur par son ID ou nom d'utilisateur",
        description: "Récupération de tous les utilisateurs ou un utilisateur par son par son ID ou nom d'utilisateur",
        tags: ['Utilisateur'],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "username",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur/s récupéré/s avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
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
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Can't use 2 parameters. Only 1 or 0 is allowed")
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
    public function get(Request $request): Response {
        $id = $request->query->get('id');
        $username = $request->query->get('username');
        $data = null;

        if ($id && $username) {
            return new JsonResponse(['error' => "Can't use 2 parameters. Only 1 or 0 is allowed"], 400);
        }

        if (!$id && !$username) {
            $data = $this->userRepository->findAll();
        }
        elseif ($id && !$username) {
            $data = $this->userRepository->find($id);
        }
        elseif (!$id && $username) {
            $data = $this->userRepository->findOneByUsername($username);
        }
        elseif (!$data) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse($this->jsonConverter->encodeToJson($data), 200, [], true);
    }

}
