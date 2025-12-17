<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\JsonConverter;
use DateTime;
use DateTimeZone;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\Clock\now;

class ModerationController extends AbstractController {

    private JsonConverter $jsonConverter;
    private UserRepository $userRepository;
    private PublicationRepository $publicationRepository;

    public function __construct(JsonConverter $jsonConverter, UserRepository $userRepository, PublicationRepository $publicationRepository) {
        $this->jsonConverter = $jsonConverter;
        $this->userRepository = $userRepository;
        $this->publicationRepository = $publicationRepository;
    }

    #[Route('/api/users/ban/id/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/ban/id/{id}',
        summary: "Bannir un utilisateur par son ID",
        description: "Bannissement d'un utilisateur par son ID",
        tags: ['Moderation'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id', 'banDurationDays'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'int', example: 1),
                    new OA\Property(property: 'banDurationDays', type: 'int', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur banni avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameter 'user_id' and 'banDurationDays' required")
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
                        new OA\Property(property: 'error', type: 'string', example:'You are not allowed to ban this user')
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
    public function ban(Request $request): JsonResponse {
        
        $json = $request->getContent();
        $data = json_decode($json, true);
        $id = $data["user_id"];
        $banDurationDays = $data["banDurationDays"];
        if (!$id || !$banDurationDays) {
            return new JsonResponse(['error' => "Parameter 'user_id' and 'banDurationDays' required"], 400);
        }
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $userIsAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $userIsMod = in_array('ROLE_MOD', $user->getRoles());

        if ((!$isMod && !$isAdmin) || ($isCurrentUser) || ($isMod && ($userIsMod || $userIsAdmin)) || ($userIsAdmin)) {
            return new JsonResponse(['error' => 'You are not allowed to ban this user'], 403);
        }
        $date = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $interval = '+' . $banDurationDays . ' days';
        $date->modify($interval);
        $this->userRepository->updateBannedUntil($user, value: $date);
        $data = $this->jsonConverter->encodeToJson($user, ['user', 'user_private']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users/deban/id/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/deban/id/{id}',
        summary: "Débannir un utilisateur par son ID",
        description: "Débannissement d'un utilisateur par son ID",
        tags: ['Moderation'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur débanni avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'Missing token / Invalid token')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Refusé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example:'You are not allowed to deban this user')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Confict',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'This user is already unbanned')
                    ]
                )
            )
        ]
    )]
    public function deban(int $id): JsonResponse {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 404);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $userIsAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $userIsMod = in_array('ROLE_MOD', $user->getRoles());

        if ((!$isMod && !$isAdmin) || ($isCurrentUser) || ($isMod && ($userIsMod || $userIsAdmin)) || ($userIsAdmin)) {
            return new JsonResponse(['error' => 'You are not allowed to deban this user'], 403);
        }
        if ($user->getBannedUntil() < now()) {
            return new JsonResponse(['error' => 'This user is already unbanned'], 409);
        }

        $this->userRepository->updateBannedUntil($user, new DateTime('1970-01-01 00:00:00', new DateTimeZone('Europe/Paris')));
        $data = $this->jsonConverter->encodeToJson($user, ['user', 'user_private']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/publications/lock/id/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/publications/lock/id/{id}',
        summary: "Verrouiller une publication",
        description: "Verrouillage d'une publication",
        tags: ['Moderation'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Publication verrouillée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'description', type: 'string', example: 'Cultivation de mes plantes'),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-11-27 12:06:32'),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'is_lock', type: 'boolean', example: true)
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to lock this publication')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication not found')
                    ]
                )
            )
        ]
    )]
    public function lock(int $id): JsonResponse {
        $publication = $this->publicationRepository->find($id);

        if (!$publication) {
            return new JsonResponse(['error' => "Publication not found"], 404);
        }

        $currentUser = $this->getUser();
        $isMod = count(array_intersect($currentUser->getRoles(), ['ROLE_MOD', 'ROLE_ADMIN'])) > 0;

        if (!$isMod) {
            return new JsonResponse(['error' => 'You are not allowed to lock this publication'], 403);
        }

        $publication = $this->publicationRepository->lock($publication);

        $data = $this->jsonConverter->encodeToJson($publication, ['publication', 'publication_private']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/publications/delock/id/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/publications/delock/id/{id}',
        summary: "Déverrouiller une publication",
        description: "Déverrouillage d'une publication",
        tags: ['Moderation'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Publication déverrouillée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'description', type: 'string', example: 'Cultivation de mes plantes'),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-11-27 12:06:32'),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'is_lock', type: 'boolean', example: false)
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to delock this publication')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication not found')
                    ]
                )
            )
        ]
    )]
    public function delock(int $id): JsonResponse {
        $publication = $this->publicationRepository->find($id);

        if (!$publication) {
            return new JsonResponse(['error' => "Publication not found"], 404);
        }

        $currentUser = $this->getUser();
        $isMod = count(array_intersect($currentUser->getRoles(), ['ROLE_MOD', 'ROLE_ADMIN'])) > 0;

        if (!$isMod) {
            return new JsonResponse(['error' => 'You are not allowed to lock this publication'], 403);
        }

        $publication = $this->publicationRepository->delock($publication);

        $data = $this->jsonConverter->encodeToJson($publication, ['publication', 'publication_private']);
        return new JsonResponse($data, 200, [], true);
    }

}
