<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PublicationRepository;
use App\Service\JsonConverter;
use App\Trait\ModerationTrait;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class ModerationController extends AbstractController
{

    use ModerationTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly JsonConverter $jsonConverter
    ) {}

    #[Route('/api/moderation/ban', methods: ['POST'])]
    #[OA\Post(
        path: '/api/moderation/ban',
        summary: "Bannir un utilisateur",
        description: "Bannissement d'un utilisateur (MOD/ADMIN uniquement)",
        tags: ['Modération'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id', 'duration'],
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 2),
                    new OA\Property(property: 'duration', type: 'integer', example: 7, description: 'Durée du ban en jours')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur banni avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 2),
                        new OA\Property(property: 'username', type: 'string', example: 'user2'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                        new OA\Property(property: 'publications', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'banned_until', type: 'string', example: '2025-12-10 12:00:00')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'id' and 'duration' required")
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to ban this user')
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
    public function ban(Request $request): JsonResponse
    {
        if (!$this->isModerator()) {
            return $this->json(['error' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (!($id = $data['id'] ?? null) || !($duration = $data['duration'] ?? null)) {
            return $this->json(['error' => "Parameters 'id' and 'duration' required"], Response::HTTP_BAD_REQUEST);
        }

        if (!($user = $this->userRepository->find($id))) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModerateUser($user)) {
            return $this->json(['error' => 'You are not allowed to ban this user'], Response::HTTP_FORBIDDEN);
        }

        $bannedUntil = (new DateTime())->modify("+{$duration} days");
        $user = $this->userRepository->updateBannedUntil($user, $bannedUntil);

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/moderation/unban', methods: ['POST'])]
    #[OA\Post(
        path: '/api/moderation/unban',
        summary: "Débannir un utilisateur",
        description: "Débannissement d'un utilisateur (MOD/ADMIN uniquement)",
        tags: ['Modération'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id'],
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur débanni avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 2),
                        new OA\Property(property: 'username', type: 'string', example: 'user2'),
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to unban this user')
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
    public function unban(Request $request): JsonResponse
    {
        if (!$this->isModerator()) {
            return $this->json(['error' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (!($id = $data['id'] ?? null)) {
            return $this->json(['error' => "Parameter 'id' required"], Response::HTTP_BAD_REQUEST);
        }

        if (!($user = $this->userRepository->find($id))) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModerateUser($user)) {
            return $this->json(['error' => 'You are not allowed to unban this user'], Response::HTTP_FORBIDDEN);
        }

        $unbannedDate = new DateTime('1970-01-01 00:00:00');
        $user = $this->userRepository->updateBannedUntil($user, $unbannedDate);

        $data = $this->jsonConverter->encodeToJson($user, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/moderation/lock/publication/id/{id}', methods: ['POST'])]
    #[OA\Post(
        path: '/api/moderation/lock/publication/id/{id}',
        summary: "Verrouiller une publication",
        description: "Verrouillage d'une publication (MOD/ADMIN uniquement)",
        tags: ['Modération'],
        responses: [
            new OA\Response(
                response: 200,
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
                        new OA\Property(property: 'is_locked', type: 'boolean', example: true)
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
                        new OA\Property(property: 'error', type: 'string', example: 'Insufficient permissions')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication not found')
                    ]
                )
            )
        ]
    )]
    public function lockPublication(int $id): JsonResponse
    {
        if (!$this->isModerator()) {
            return $this->json(['error' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        if (!($publication = $this->publicationRepository->find($id))) {
            return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
        }

        $publication = $this->publicationRepository->lock($publication);

        $data = $this->jsonConverter->encodeToJson($publication, ['publication']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/moderation/unlock/publication/id/{id}', methods: ['POST'])]
    #[OA\Post(
        path: '/api/moderation/unlock/publication/id/{id}',
        summary: "Déverrouiller une publication",
        description: "Déverrouillage d'une publication (MOD/ADMIN uniquement)",
        tags: ['Modération'],
        responses: [
            new OA\Response(
                response: 200,
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
                        new OA\Property(property: 'is_locked', type: 'boolean', example: false)
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
                        new OA\Property(property: 'error', type: 'string', example: 'Insufficient permissions')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication not found')
                    ]
                )
            )
        ]
    )]
    public function unlockPublication(int $id): JsonResponse
    {
        if (!$this->isModerator()) {
            return $this->json(['error' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        if (!($publication = $this->publicationRepository->find($id))) {
            return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
        }

        $publication = $this->publicationRepository->delock($publication);

        $data = $this->jsonConverter->encodeToJson($publication, ['publication']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
