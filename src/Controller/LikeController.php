<?php

namespace App\Controller;

use App\Repository\LikeRepository;
use App\Repository\PublicationRepository;
use App\Repository\CommentRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class LikeController extends AbstractController {

    private JsonConverter $jsonConverter;
    private PublicationRepository $publicationRepository;
    private LikeRepository $likeRepository;
    private CommentRepository $commentRepository;

    public function __construct(JsonConverter $jsonConverter, LikeRepository $likeRepository, PublicationRepository $publicationRepository, CommentRepository $commentRepository) {
        $this->jsonConverter = $jsonConverter;
        $this->publicationRepository = $publicationRepository;
        $this->likeRepository = $likeRepository;
        $this->commentRepository = $commentRepository;
    }

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
    public function likePublication(int $id): JsonResponse {
        $publication = $this->publicationRepository->find($id);

        if (!$publication) {
            return new JsonResponse(['error' => 'Publication not found'], 404);
        }

        $currentUser = $this->getUser();
        if ($this->likeRepository->findLikeByUserAndPublication($currentUser, $publication)) {
            return new JsonResponse(['error' => 'You already liked it'], 409);
        }

        $like = $this->likeRepository->create($this->getUser(), $publication, null);

        $data = $this->jsonConverter->encodeToJson($like);
        return new JsonResponse($data, 201, [], true);
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
                        new OA\Property(property: 'error', type: 'string', example: 'You already liked it')
                    ]
                )
            )
        ]
    )]
    public function likeComment(int $id): JsonResponse {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'Comment not found'], 404);
        }

        $currentUser = $this->getUser();
        if ($this->likeRepository->findLikeByUserAndComment($currentUser, $comment)) {
            return new JsonResponse(['error' => 'You already liked it'], 409);
        }

        $like = $this->likeRepository->create($this->getUser(), null, $comment);

        $data = $this->jsonConverter->encodeToJson($like);
        return new JsonResponse($data, 201, [], true);
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
    public function delete(int $id): JsonResponse {
        $like = $this->likeRepository->find($id);
        if (!$like) {
            return new JsonResponse(['error' => 'Like not found'], 404);
        }

        $currentUser = $this->getUser();
        if ($currentUser->getUserIdentifier() !== $like->getUser()->getUserIdentifier()) {
            return new JsonResponse(['error' => 'You are not allowed to delete this like'], 403);
        }

        $this->likeRepository->delete($like);
        return new JsonResponse([], 200);
    }

}
