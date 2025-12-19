<?php

namespace App\Controller;

use App\Repository\DislikeRepository;
use App\Repository\PublicationRepository;
use App\Repository\CommentRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class DislikeController extends AbstractController {

    private JsonConverter $jsonConverter;
    private PublicationRepository $publicationRepository;
    private DislikeRepository $dislikeRepository;
    private CommentRepository $commentRepository;

    public function __construct(JsonConverter $jsonConverter, DislikeRepository $dislikeRepository, PublicationRepository $publicationRepository, CommentRepository $commentRepository) {
        $this->jsonConverter = $jsonConverter;
        $this->publicationRepository = $publicationRepository;
        $this->dislikeRepository = $dislikeRepository;
        $this->commentRepository = $commentRepository;
    }

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
            ),
            new OA\Response(
                response: 403,
                description: 'Refusé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to dislike your own publication')
                    ]
                )
            )
        ]
    )]
    public function dislikePublication(int $id): JsonResponse {
        $publication = $this->publicationRepository->find($id);

        if (!$publication) {
            return new JsonResponse(['error' => 'Publication not found'], 404);
        }

        $currentUser = $this->getUser();
        if ($this->dislikeRepository->findDislikeByUserAndPublication($currentUser, $publication)) {
            return new JsonResponse(['error' => 'You already disliked it'], 409);
        }

        if ($publication->getUser() === $currentUser) {
            return new JsonResponse(['error' => 'You are not allowed to dislike your own publication'], 403);
        }

        $dislike = $this->dislikeRepository->create($this->getUser(), $publication, null);

        $data = $this->jsonConverter->encodeToJson($dislike);
        return new JsonResponse($data, 201, [], true);
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
                        new OA\Property(property: 'publication', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
            ),
            new OA\Response(
                response: 403,
                description: 'Refusé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to dislike your own comment')
                    ]
                )
            )
        ]
    )]
    public function dislikeComment(int $id): JsonResponse {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'Comment not found'], 404);
        }

        $currentUser = $this->getUser();
        if ($this->dislikeRepository->findDislikeByUserAndComment($currentUser, $comment)) {
            return new JsonResponse(['error' => 'You already disliked it'], 409);
        }

        if ($comment->getUser() === $currentUser) {
            return new JsonResponse(['error' => 'You are not allowed to dislike your own comment'], 403);
        }

        $dislike = $this->dislikeRepository->create($this->getUser(), null, $comment);

        $data = $this->jsonConverter->encodeToJson($dislike);
        return new JsonResponse($data, 201, [], true);
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
    public function delete(int $id): JsonResponse {
        $dislike = $this->dislikeRepository->find($id);
        if (!$dislike) {
            return new JsonResponse(['error' => 'Dislike not found'], 404);
        }

        $currentUser = $this->getUser();
        if ($currentUser->getUserIdentifier() !== $dislike->getUser()->getUserIdentifier()) {
            return new JsonResponse(['error' => 'You are not allowed to delete this dislike'], 403);
        }

        $this->dislikeRepository->delete($dislike);
        return new JsonResponse([], 200);
    }

}
