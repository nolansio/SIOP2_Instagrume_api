<?php

namespace App\Controller;

use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Repository\PublicationRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class LikeController extends AbstractController {

    public function __construct(private UserRepository $userRepository, private JsonConverter $jsonConverter, private LikeRepository $likeRepository, private PublicationRepository $publicationRepository, private CommentRepository $commentRepository) {
    }

    #[Route('/api/likes/publication', methods: ['POST'])]
    #[OA\Post(
        path: '/api/likes/publication',
        summary: "Ajoute un like à une publication",
        description: "Ajoute un like à une publication",
        tags: ['Like'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['publication_id'],
                properties: [
                    new OA\Property(property: 'publication_id', type: 'int', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Like créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 7),
                        new OA\Property(property: 'user', type: 'user object', example: 'user'),
                        new OA\Property(property: 'publication', type: 'publication object', example: "publication object"),
                        new OA\Property(property: 'comment', type: 'comment object', example: "publication object")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameter 'publication_id' required")
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to add this like')
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
    public function insertIntoPublication(Request $request): Response {
        $data = json_decode($request->getContent(), true);
        $publication_id = $data['publication_id'] ?? null;

        if (!$publication_id) {
            return new JsonResponse(['error' => "Parameter 'publication_id' required"], 400);
        }
        if (!$this->publicationRepository->find($publication_id)) {
            return new JsonResponse(['error' => "Publication not found"], 404);
        }

        $publication = $this->publicationRepository->find($publication_id);
        $currentUser = $this->getUser();
        $userAlreadyLikedPublication = $this->likeRepository->findLikeByUserAndPublication($currentUser, $publication);
        if ($userAlreadyLikedPublication) {
            return new JsonResponse(['error' => "You already liked it"], 409);
        }
        $author = $publication->getUser();
        if (!$author) {
            return new JsonResponse(['error' => 'You are not allowed to add this like'], 403);
        }

        $like = new Like();
        $like->setPublication($publication);
        $like->setUser($currentUser);
        $this->likeRepository->create($like);
        $data = $this->jsonConverter->encodeToJson($like, ['all']);
        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/api/likes/commentaire', methods: ['POST'])]
    #[OA\Post(
        path: '/api/likes/commentaire',
        summary: "Ajoute un like à une commentaire",
        description: "Ajoute un like à une commentaire",
        tags: ['Like'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['commentaire_id'],
                properties: [
                    new OA\Property(property: 'commentaire_id', type: 'int', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Like créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 7),
                        new OA\Property(property: 'user', type: 'user object', example: 'user'),
                        new OA\Property(property: 'publication', type: 'publication object', example: "publication object"),
                        new OA\Property(property: 'comment', type: 'comment object', example: "publication object")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameter 'commentaire_id' required")
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to add this like')
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
    public function insertIntoCommentaire(Request $request): Response {
        $data = json_decode($request->getContent(), true);
        $commentaire_id = $data['publication_id'] ?? null;

        if (!$commentaire_id) {
            return new JsonResponse(['error' => "Parameter 'commentaire_id' required"], 400);
        }
        if (!$this->commentRepository->find($commentaire_id)) {
            return new JsonResponse(['error' => "Comment not found"], 404);
        }

        $comment = $this->commentRepository->find($commentaire_id);
        $currentUser = $this->getUser();
        $userAlreadyLikedComment = $this->likeRepository->findLikeByUserAndComment($currentUser, $comment);
        if ($userAlreadyLikedComment) {
            return new JsonResponse(['error' => "You already liked it"], 409);
        }
        $author = $comment->getUser();
        if (!$author) {
            return new JsonResponse(['error' => 'You are not allowed to add this like'], 403);
        }

        $like = new Like();
        $like->setComment($comment);
        $like->setUser($currentUser);
        $this->likeRepository->create($like);
        $data = $this->jsonConverter->encodeToJson($like, ['all']);
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
                        new OA\Property(property: 'error', type: 'string', example: "'Parameter 'id' is required'")
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
    public function delete($id): Response {
        if (!$id) {
            return new JsonResponse(['error' => "Parameters 'id' is required"], 400);
        }        
        if (!$this->likeRepository->find($id)) {
            return new JsonResponse(['error' => "Like not found"], 404);
        }

        $like = $this->likeRepository->find($id);
        $author = $like->getUser();
        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $author->getUserIdentifier();
        if (!$isCurrentUser) {
            return new JsonResponse(['error' => 'You are not allowed to delete this like'], 403);
        }
        $this->likeRepository->delete($like);
        return new JsonResponse([], 200);
    }

}
