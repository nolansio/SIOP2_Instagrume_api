<?php

namespace App\Controller;

use App\Repository\DislikeRepository;
use App\Repository\LikeRepository;
use App\Repository\PublicationRepository;
use App\Repository\CommentRepository;
use App\Service\JsonConverter;
use App\Trait\OwnershipCheckTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class DislikeController extends AbstractController
{
    use OwnershipCheckTrait;

    public function __construct(
        private readonly JsonConverter $jsonConverter,
        private readonly DislikeRepository $dislikeRepository,
        private readonly LikeRepository $likeRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly CommentRepository $commentRepository
    ) {}

    #[Route('/api/dislikes/publication/id/{id}', methods: ['POST'])]
    #[OA\Post(
        path: '/api/dislikes/publication/id/{id}',
        summary: "Ajouter un dislike à une publication",
        description: "Ajout d'un dislike à une publication",
        tags: ['Dislike'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Dislike ajouté avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to dislike your own publication')
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
                        new OA\Property(property: 'error', type: 'string', example: 'You already disliked it')
                    ]
                )
            )
        ]
    )]
    public function dislikePublication(int $id): JsonResponse
    {
        if (!($publication = $this->publicationRepository->find($id))) {
            return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();

        if ($this->dislikeRepository->findDislikeByUserAndPublication($currentUser, $publication)) {
            return $this->json(['error' => 'You already disliked it'], Response::HTTP_CONFLICT);
        }

        if ($this->isOwnContent($publication->getUser())) {
            return $this->json(['error' => 'You are not allowed to dislike your own publication'], Response::HTTP_FORBIDDEN);
        }

        // Supprimer le like s'il existe
        $existingLike = $this->likeRepository->findLikeByUserAndPublication($currentUser, $publication);
        if ($existingLike) {
            $this->likeRepository->delete($existingLike);
        }

        $dislike = $this->dislikeRepository->create($currentUser, $publication, null);

        $data = $this->jsonConverter->encodeToJson($dislike);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/dislikes/comment/id/{id}', methods: ['POST'])]
    #[OA\Post(
        path: '/api/dislikes/comment/id/{id}',
        summary: "Ajouter un dislike à un commentaire",
        description: "Ajout d'un dislike à un commentaire",
        tags: ['Dislike'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Dislike ajouté avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to dislike your own comment')
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
                        new OA\Property(property: 'error', type: 'string', example: 'You already disliked it')
                    ]
                )
            )
        ]
    )]
    public function dislikeComment(int $id): JsonResponse
    {
        if (!($comment = $this->commentRepository->find($id))) {
            return $this->json(['error' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();

        if ($this->dislikeRepository->findDislikeByUserAndComment($currentUser, $comment)) {
            return $this->json(['error' => 'You already disliked it'], Response::HTTP_CONFLICT);
        }

        if ($this->isOwnContent($comment->getUser())) {
            return $this->json(['error' => 'You are not allowed to dislike your own comment'], Response::HTTP_FORBIDDEN);
        }

        // Supprimer le like s'il existe
        $existingLike = $this->likeRepository->findLikeByUserAndComment($currentUser, $comment);
        if ($existingLike) {
            $this->likeRepository->delete($existingLike);
        }

        $dislike = $this->dislikeRepository->create($currentUser, null, $comment);

        $data = $this->jsonConverter->encodeToJson($dislike);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/dislikes/id/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/dislikes/id/{id}',
        summary: "Supprimer un dislike",
        description: "Suppression d'un dislike",
        tags: ['Dislike'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dislike supprimé avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to delete this dislike')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Dislike not found')
                    ]
                )
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        if (!($dislike = $this->dislikeRepository->find($id))) {
            return $this->json(['error' => 'Dislike not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->isOwnContent($dislike->getUser())) {
            return $this->json(['error' => 'You are not allowed to delete this dislike'], Response::HTTP_FORBIDDEN);
        }

        $this->dislikeRepository->delete($dislike);
        return $this->json([], Response::HTTP_OK);
    }
}
