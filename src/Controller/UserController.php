<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use OpenApi\Attributes as OA;

class UserController extends AbstractController
{

    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(
        private readonly JsonConverter $jsonConverter,
        private readonly UserRepository $userRepository,
        private readonly TagAwareCacheInterface $cacheUsers
    ) {}

    #[Route('/api/users', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: "Récupérer tous les utilisateurs",
        description: "Récupération de tous les utilisateurs avec pagination",
        tags: ['Utilisateur'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Numéro de page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Nombre d\'éléments par page (max 100)',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 50, example: 50)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateurs récupérés avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'admin'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_ADMIN', 'ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'banned_until', type: 'string', example: '1970-01-01 00:00:00')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
                    ]
                )
            )
        ]
    )]
    public function getAll(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 50)));

        $cacheKey = "users_page_{$page}_limit_{$limit}";

        $result = $this->cacheUsers->get($cacheKey, function (ItemInterface $item) use ($page, $limit) {
            $item->expiresAfter(600); // 10 minutes
            $item->tag(['users']);
            return $this->userRepository->findPaginated($page, $limit);
        });

        $data = $this->jsonConverter->encodeToJson($result['data'], ['user']);

        return new JsonResponse($data, Response::HTTP_OK, [
            'X-Total-Count' => $result['total'],
            'X-Total-Pages' => $result['pages'],
            'X-Current-Page' => $result['current_page'],
            'X-Per-Page' => $result['per_page']
        ], true);
    }

    #[Route('/api/users/id/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/id/{id}',
        summary: "Récupérer un utilisateur par son ID",
        description: "Récupération d'un utilisateur par son ID",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'admin'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_ADMIN', 'ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'banned_until', type: 'string', example: '1970-01-01 00:00:00')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
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
    public function get(int $id): JsonResponse
    {
        if (!($user = $this->userRepository->findOneByIdOptimized($id))) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/username/{username}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/username/{username}',
        summary: "Récupérer un utilisateur par son username",
        description: "Récupération d'un utilisateur par son username",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'admin'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_ADMIN', 'ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'banned_until', type: 'string', example: '1970-01-01 00:00:00')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
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
    public function getByUsername(string $username): JsonResponse
    {
        if (!($user = $this->userRepository->findOneByUsername($username))) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/search/{username}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/search/{username}',
        summary: "Rechercher des utilisateurs par username",
        description: "Recherche d'utilisateurs par username (recherche partielle)",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateurs trouvés avec succès',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['admin', 'admin2', 'administrator']
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
                    ]
                )
            )
        ]
    )]
    public function searchByUsername(string $username): JsonResponse
    {
        $usernames = $this->userRepository->findUsernamesByUsername($username);
        return $this->json($usernames);
    }

    #[Route('/api/users', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: "Créer un utilisateur",
        description: "Création d'un utilisateur",
        tags: ['Utilisateur'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'newuser'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utilisateur créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 3),
                        new OA\Property(property: 'username', type: 'string', example: 'newuser'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'banned_until', type: 'string', example: '1970-01-01 00:00:00')
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
                response: 409,
                description: 'Conflit',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Username already taken')
                    ]
                )
            )
        ]
    )]
    public function insert(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!($username = $data['username'] ?? null) || !($password = $data['password'] ?? null)) {
            return $this->json(['error' => "Parameters 'username' and 'password' required"], Response::HTTP_BAD_REQUEST);
        }

        if ($this->userRepository->findOneByUsername($username)) {
            return $this->json(['error' => 'Username already taken'], Response::HTTP_CONFLICT);
        }

        $user = $this->userRepository->create($username, $password);

        $this->cacheUsers->invalidateTags(['users']);

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/users', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users',
        summary: "Modifier un utilisateur",
        description: "Modification d'un utilisateur (username, password et/ou avatar)",
        tags: ['Utilisateur'],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'multipart/form-data' => new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['id'],
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'username', type: 'string', example: 'newusername'),
                            new OA\Property(property: 'password', type: 'string', example: 'newpassword'),
                            new OA\Property(property: 'avatar', type: 'string', format: 'binary')
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur modifié avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'newusername'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'banned_until', type: 'string', example: '1970-01-01 00:00:00')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameter 'id' required")
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
                response: 403,
                description: 'Refusé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to update this user')
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
            ),
            new OA\Response(
                response: 409,
                description: 'Conflit',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Username already taken')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $data = $this->parseMultipartPutRequest($request);

        if (!($id = $data['id'] ?? null)) {
            return $this->json(['error' => "Parameter 'id' required"], Response::HTTP_BAD_REQUEST);
        }

        if (!($user = $this->userRepository->find($id))) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModifyUser($user)) {
            return $this->json(['error' => 'You are not allowed to update this user'], Response::HTTP_FORBIDDEN);
        }

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $avatar = $data['avatar'] ?? null;

        if ($username && $password) {
            $existingUser = $this->userRepository->findOneByUsername($username);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return $this->json(['error' => 'Username already taken'], Response::HTTP_CONFLICT);
            }
            $user = $this->userRepository->update($user, $username, $password);
        } elseif ($username) {
            $existingUser = $this->userRepository->findOneByUsername($username);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return $this->json(['error' => 'Username already taken'], Response::HTTP_CONFLICT);
            }
            $user = $this->userRepository->updateUsername($user, $username);
        } elseif ($password) {
            $user = $this->userRepository->updatePassword($user, $password);
        }

        if ($avatar instanceof UploadedFile) {
            $extension = strtolower($avatar->getClientOriginalExtension());
            if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
                return $this->json(['error' => 'Invalid image format'], Response::HTTP_BAD_REQUEST);
            }
            $user = $this->userRepository->updateAvatar($user, $avatar);
        }

        $this->cacheUsers->invalidateTags(['users']);

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/id/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/users/id/{id}',
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
                response: 403,
                description: 'Refusé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to delete this user')
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
    public function delete(int $id): JsonResponse
    {
        if (!($user = $this->userRepository->find($id))) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModifyUser($user)) {
            return $this->json(['error' => 'You are not allowed to delete this user'], Response::HTTP_FORBIDDEN);
        }

        $this->userRepository->delete($user);

        $this->cacheUsers->invalidateTags(['users']);

        return $this->json([], Response::HTTP_OK);
    }

    private function canModifyUser(User $user): bool
    {
        $currentUser = $this->getUser();

        $isCurrentUser = $currentUser->getUserIdentifier() === $user->getUserIdentifier();

        $currentUserRoles = $currentUser->getRoles();
        $targetRoles = $user->getRoles();

        $isAdmin = in_array('ROLE_ADMIN', $currentUserRoles);
        $targetIsAdmin = in_array('ROLE_ADMIN', $targetRoles);

        if ($isCurrentUser) {
            return true;
        }

        if ($isAdmin && !$targetIsAdmin) {
            return true;
        }

        return false;
    }

    private function parseMultipartPutRequest(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type', '');

        if (!str_starts_with($contentType, 'multipart/form-data')) {
            return json_decode($request->getContent(), true) ?? [];
        }

        $data = [];
        $boundary = null;

        if (preg_match('/boundary=(.*)$/', $contentType, $matches)) {
            $boundary = $matches[1];
        }

        if (!$boundary) {
            return $data;
        }

        $rawData = $request->getContent();
        $parts = array_slice(explode("--$boundary", $rawData), 1);

        foreach ($parts as $part) {
            if ($part === "--\r\n" || $part === "--") {
                continue;
            }

            $part = ltrim($part, "\r\n");
            [$rawHeaders, $body] = explode("\r\n\r\n", $part, 2);
            $body = substr($body, 0, -2);

            $headers = [];
            foreach (explode("\r\n", $rawHeaders) as $header) {
                [$name, $value] = explode(':', $header, 2);
                $headers[strtolower($name)] = trim($value);
            }

            $contentDisposition = $headers['content-disposition'] ?? '';
            if (preg_match('/name="([^"]+)"/', $contentDisposition, $matches)) {
                $fieldName = $matches[1];

                if (preg_match('/filename="([^"]+)"/', $contentDisposition, $filenameMatches)) {
                    $filename = $filenameMatches[1];
                    $tmpPath = tempnam(sys_get_temp_dir(), 'upload_');
                    file_put_contents($tmpPath, $body);
                    $data[$fieldName] = new UploadedFile($tmpPath, $filename, $headers['content-type'] ?? null, null, true);
                } else {
                    $data[$fieldName] = $body;
                }
            }
        }

        return $data;
    }
}
