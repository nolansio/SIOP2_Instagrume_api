<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\ImageService;
use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Doctrine\Persistence\ManagerRegistry;


class UserController extends AbstractController {

    private JsonConverter $jsonConverter;
    private UserRepository $userRepository;
    private ImageRepository $imageRepository;

    public function __construct(JsonConverter $jsonConverter, UserRepository $userRepository, ImageRepository $imageRepository) {
        $this->jsonConverter = $jsonConverter;
        $this->userRepository = $userRepository;
        $this->imageRepository = $imageRepository;
    }

    #[Route('/api/users', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: "Récupérer tout les utilisateurs",
        description: "Récupération de tous les utilisateurs",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateurs récupérés avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'user'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'is_banned', type: 'boolean', example: false)
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
    public function getAll(): JsonResponse {
        $users = $this->userRepository->findAll();

        $data = $this->jsonConverter->encodeToJson($users, ['user']);
        return new JsonResponse($data, 200, [], true);
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
                        new OA\Property(property: 'username', type: 'string', example: 'user'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'is_banned', type: 'boolean', example: false)
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
    public function getById($id): JsonResponse {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users/username/{username}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/username/{username}',
        summary: "Récupérer un utilisateur par son nom d'utilisateur",
        description: "Récupération d'un utilisateur par son nom d'utilisateur",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'user'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'is_banned', type: 'boolean', example: false)
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
    public function getByUsername($username): JsonResponse {
        $user = $this->userRepository->findOneByUsername($username);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users/search/{username}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/search/{username}',
        summary: "Récupérer plusieurs noms d'utilisateur par un nom d'utilisateur",
        description: "Récupération de plusieurs noms d'utilisateur par un nom d'utilisateur",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Noms d'utilisateur récupérés avec succès",
                content: new OA\JsonContent(
                    example: ['admin', 'albert', 'moderator']
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
        ]
    )]
    public function getUsernamesByUsername($username): JsonResponse {
        $usernames = $this->userRepository->findUsernamesByUsername($username);

        $data = $this->jsonConverter->encodeToJson($usernames, ['user']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users/isbanned/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/isbanned/{id}',
        summary: "Récupérer si l'utilisateur est banni par son ID",
        description: "Récupération de si l'utilisateur est banni par son ID",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Réponse envoyée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'value', type: 'string', example: false)
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
    public function getIsBannedById($id): JsonResponse {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);

        }

        $data = $this->jsonConverter->encodeToJson(["value" => $user->isBanned()]);
        return new JsonResponse($data, 200, [], true);
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
                    new OA\Property(property: 'username', type: 'string', example: 'toto'),
                    new OA\Property(property: 'password', type: 'string', example: 'password')
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
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'is_banned', type: 'boolean', example: false)
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
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflit',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Username already exists")
                    ]
                )
            )
        ]
    )]
    public function insert(Request $request): JsonResponse {
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

        $data = $this->jsonConverter->encodeToJson($user, ['user', 'private_user']);
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
            content: [
                "multipart/form-data" => new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(
                    required: ["id"],
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: "1"),
                        new OA\Property(property: 'username', type: 'string', example: "user"),
                        new OA\Property(property: 'password', type: 'string', example: "password"),
                        new OA\Property(property: "avatar", type: "string", format: "binary")
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
                    new OA\Property(property: 'username', type: 'string', example: 'user'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                    new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                    new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                    new OA\Property(property: 'is_banned', type: 'boolean', example: false)
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
    public function update(Request $request): JsonResponse {
        // === Fix PUT + multipart ===
        if ($request->getMethod() === 'PUT') {
            $contentType = $request->headers->get('Content-Type');
            if (str_contains($contentType, 'multipart/form-data')) {
                $boundary = substr($contentType, strpos($contentType, "boundary=") + 9);
                $raw = file_get_contents("php://input");
                $blocks = preg_split("/-+$boundary/", $raw);
                array_pop($blocks);

                foreach ($blocks as $block) {
                    if (empty($block)) continue;

                    if (str_contains($block, "filename=")) {
                        preg_match('/name="([^"]*)"; filename="([^"]*)"/', $block, $matches);
                        preg_match('/Content-Type: ([^\r\n]+)/', $block, $type);
                        preg_match('/\r\n\r\n(.*)\r\n$/s', $block, $body);

                        $tmp = tempnam(sys_get_temp_dir(), "php");
                        file_put_contents($tmp, $body[1]);

                        $_FILES[$matches[1]] = [
                            'name' => $matches[2],
                            'tmp_name' => $tmp,
                            'type' => $type[1],
                            'error' => 0,
                            'size' => filesize($tmp)
                        ];
                    } else {
                        preg_match('/name="([^"]*)"\r\n\r\n(.*)\r\n$/s', $block, $matches);
                        $_POST[$matches[1]] = $matches[2];
                    }
                }

                $request->request->replace($_POST);
                $request->files->replace($_FILES);
            }
        }
        // === Fin du fix ===

        $id = $request->request->get('id');
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $avatar   = $request->files->get('avatar');

        if (!$id) {
            return new JsonResponse(['error' => "Parameter 'id' required"], 400);
        }

        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() === $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles()) || in_array('ROLE_ADMIN', $currentUser->getRoles());

        if (!$isCurrentUser && !$isMod) {
            return new JsonResponse(['error' => 'You are not allowed to update this user'], 403);
        }

        if ($username) {
            $user = $this->userRepository->updateUsername($username, $user);
        }
        if ($password) {
            $user = $this->userRepository->updatePassword($password, $user);
        }
        if ($avatar) {
            $isFormatOk = $this->userRepository->updateAvatar($this->getUser(), $avatar);
            if (!$isFormatOk) {
                return new JsonResponse(['error' => "Bad image extension"], 415);
            }
        }

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, 200, [], true);
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
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameter 'id' is required")
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
    public function delete($id): JsonResponse {
        if (!$id) {
            return new JsonResponse(['error' => "Parameters 'id' is required"], 400);
        }

        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles()) || in_array('ROLE_ADMIN', $currentUser->getRoles());

        if (!$isCurrentUser && !$isMod) {
            return new JsonResponse(['error' => 'You are not allowed to delete this user'], 403);
        }

        $this->userRepository->delete($user);
        return new JsonResponse([], 200);
    }

    #[Route('/api/users/myself', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/myself',
        summary: "Récupérer son utilisateur",
        description: "Récupération de son utilisateur",
        tags: ['Utilisateur'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'user'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object'), example: ['ROLE_USER']),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'is_banned', type: 'boolean', example: false)
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
    public function myself(): JsonResponse {
        $user = $this->getUser();

        $data = $this->jsonConverter->encodeToJson($user, ['user', 'private_user']);
        return new JsonResponse($data, 200, [], true);
    }

}
