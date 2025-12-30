<?php

namespace App\Controller;

use App\Repository\LikeRepository;
use App\Repository\DislikeRepository;
use App\Repository\PublicationRepository;
use App\Repository\CommentRepository;
use App\Service\JsonConverter;
use App\Trait\OwnershipCheckTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class LikeController extends AbstractController
{
    use OwnershipCheckTrait;

    public function __construct(
        private readonly JsonConverter $jsonConverter,
        private readonly LikeRepository $likeRepository,
        private readonly DislikeRepository $dislikeRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly CommentRepository $commentRepository
    ) {}

    #[Route('/api/likes/publication/id/{id}', methods: ['POST'])]
    #[OA\Post(
        path: '/api/likes/publication/id/{id}',
        summary: "Ajouter un like à une publication",
        description: "Ajout d'un like à une publication",
        tags: ['Like'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Like ajouté avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'publication', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to like your own publication')
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
            ),
            new OA\Response(
                response: 409,
                description: 'Conflit',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'You already liked it')
                    ]
                )
            )
        ]
    )]
    public function likePublication(int $id): JsonResponse
    {
        if (!($publication = $this->publicationRepository->find($id))) {
            return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();

        if ($this->likeRepository->findLikeByUserAndPublication($currentUser, $publication)) {
            return $this->json(['error' => 'You already liked it'], Response::HTTP_CONFLICT);
        }

        if ($this->isOwnContent($publication->getUser())) {
            return $this->json(['error' => 'You are not allowed to like your own publication'], Response::HTTP_FORBIDDEN);
        }

        // Supprimer le dislike s'il existe
        $existingDislike = $this->dislikeRepository->findDislikeByUserAndPublication($currentUser, $publication);
        if ($existingDislike) {
            $this->dislikeRepository->delete($existingDislike);
        }

        $like = $this->likeRepository->create($currentUser, $publication, null);

        $data = $this->jsonConverter->encodeToJson($like);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/likes/comment/id/{id}', methods: ['POST'])]
    #[OA\Post(
        path: '/api/likes/comment/id/{id}',
        summary: "Ajouter un like à un commentaire",
        description: "Ajout d'un like à un commentaire",
        tags: ['Like'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Like ajouté avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comment', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to like your own comment')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Comment not found')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflit',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'You already liked it')
                    ]
                )
            )
        ]
    )]
    public function likeComment(int $id): JsonResponse
    {
        if (!($comment = $this->commentRepository->find($id))) {
            return $this->json(['error' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();

        if ($this->likeRepository->findLikeByUserAndComment($currentUser, $comment)) {
            return $this->json(['error' => 'You already liked it'], Response::HTTP_CONFLICT);
        }

        if ($this->isOwnContent($comment->getUser())) {
            return $this->json(['error' => 'You are not allowed to like your own comment'], Response::HTTP_FORBIDDEN);
        }

        // Supprimer le dislike s'il existe
        $existingDislike = $this->dislikeRepository->findDislikeByUserAndComment($currentUser, $comment);
        if ($existingDislike) {
            $this->dislikeRepository->delete($existingDislike);
        }

        $like = $this->likeRepository->create($currentUser, null, $comment);

        $data = $this->jsonConverter->encodeToJson($like);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/likes/id/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/likes/id/{id}',
        summary: "Supprimer un like",
        description: "Suppression d'un like",
        tags: ['Like'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Like supprimé avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to delete this like')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Like not found')
                    ]
                )
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        if (!($like = $this->likeRepository->find($id))) {
            return $this->json(['error' => 'Like not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->isOwnContent($like->getUser())) {
            return $this->json(['error' => 'You are not allowed to delete this like'], Response::HTTP_FORBIDDEN);
        }

        $this->likeRepository->delete($like);
        return $this->json([], Response::HTTP_OK);
    }
}
