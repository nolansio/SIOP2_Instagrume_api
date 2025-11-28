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
        summary: "Récupère un ou un groupe d'utilisateur",
        description: "Récupération d'un ou un groupe d'utilisateur",
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
            ),
            new OA\Parameter(
                name: "search",
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
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'posts', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
    public function get(Request $request): Response {
        $id = $request->query->get('id');
        $username = $request->query->get('username');
        $search = $request->query->get('search');
        $data = null;

        $params = array_filter(['id' => $id, 'username' => $username, 'search' => $search]);
        if (count($params) > 1) {
            return new JsonResponse(['error' => "Only one parameter ('id', 'username' or 'search') is allowed"], 400);
        }

        if (!$id && !$username && !$search) {
            $users = $this->userRepository->findAll();
            $data = $this->jsonConverter->encodeToJson($users, ['public']);
        }

        if ($id) {
            $user = $this->userRepository->find($id);
            $data = $this->jsonConverter->encodeToJson($user, ['public']);
        }

        if ($username) {
            $user = $this->userRepository->findOneByUsername($username);
            $data = $this->jsonConverter->encodeToJson($user, ['public']);
        }

        if ($search) {
            $users = $this->userRepository->findManyByUsername($search);
            $data = $this->jsonConverter->encodeToJson($users, ['public']);
        }

        if (!$data) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: "Créé un utilisateur",
        description: "Création d'un utilisateur",
        tags: ['Utilisateur'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'toto'),
                    new OA\Property(property: 'password', type: 'string', example: 'P@ssw0rd')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utilisateur créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 6),
                        new OA\Property(property: 'username', type: 'string', example: 'toto'),
                        new OA\Property(property: 'user_identifier', type: 'string', example: 'P@ssw0rd'),
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
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'username' and 'password' required")
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
    public function insert(Request $request): Response {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            return new JsonResponse(['error' => "Parameters 'username' and 'password' required"], 400);
        }

        if ($this->userRepository->findOneByUsername($username)) {
            return new JsonResponse(['error' => "Username already exists"], 409);
        }

        $user = $this->userRepository->create($username, $password);
        $data = $this->jsonConverter->encodeToJson($user, ['public', 'admin', 'private']);

        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/api/users', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users',
        summary: "Mettre à jour un utilisateur",
        description: "Mise à jour d'un utilisateur",
        tags: ['Utilisateur'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id'],
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'username', type: 'string', example: "user"),
                    new OA\Property(property: 'password', type: 'string', example: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur modifié avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'albert'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
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
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'id', 'username' and 'password' required")
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
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request): Response {
        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? null;
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$id || !$username || !$password) {
            return new JsonResponse(['error' => "Parameters 'id', 'username' and 'password' required"], 400);
        }

        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());

        if (!$isCurrentUser && !$isAdmin) {
            return new JsonResponse(['error' => 'You are not allowed to update this user'], 403);
        }

        $user2 = $this->userRepository->findOneByUsername($username);
        if ($user2 && $user->getId() != $user2->getId()) {
            return new JsonResponse(['error' => "Username already exists"], 409);
        }

        $user = $this->userRepository->update($username, $password, $user);
        $data = $this->jsonConverter->encodeToJson($user, ['public', 'admin']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/users/{id}',
        summary: "Supprimer un utilisateur",
        description: "Suppression d'un utilisateur",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur supprimé avec succès',
                content: new OA\JsonContent(
                    properties: []
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
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function delete($id): Response {
        if (!$id) {
            return new JsonResponse(['error' => "Parameters 'id' is required"], 400);
        }

        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());

        if (!$isCurrentUser && !$isAdmin) {
            return new JsonResponse(['error' => 'You are not allowed to delete this user'], 403);
        }

        $this->userRepository->delete($user);

        return new JsonResponse([], 200);
    }

}
