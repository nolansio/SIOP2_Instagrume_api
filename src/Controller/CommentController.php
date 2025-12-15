<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\PublicationRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use OpenApi\Attributes as OA;

class CommentController extends AbstractController {

    private CommentRepository $commentRepository;
    private PublicationRepository $publicationRepository;
    private JsonConverter $jsonConverter;
    private UserRepository $userRepository;


    public function __construct(CommentRepository $commentRepository, JsonConverter $jsonConverter, PublicationRepository $publicationRepository, UserRepository $userRepository) {
        $this->commentRepository = $commentRepository;
        $this->publicationRepository = $publicationRepository;
        $this->jsonConverter = $jsonConverter;
        $this->userRepository = $userRepository;
    }

    #[Route('/api/comments', methods: ['GET'])]
    #[OA\Get(
        path: '/api/comments',
        summary: "Récupérer toutes les commentaires",
        description: "Récupération de toutes les commentaires",
        tags: ['Commentaire'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commentaires récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'content', type: 'string', example: "J'aime bien la seconde image"),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-12-01 11:59:33'),
                        new OA\Property(property: 'original_comment', type: 'string', example: null),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
    public function getAll(): Response {
        $comments = $this->commentRepository->findAll();
        $data = $this->jsonConverter->encodeToJson($comments, ['user']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/comments/id/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/comments/id/{id}',
        summary: "Récupérer un commentaire par son ID",
        description: "Récupération d'un commentaire par son ID",
        tags: ['Commentaire'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commentaire récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'content', type: 'string', example: "J'aime bien la seconde image"),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-12-01 11:59:33'),
                        new OA\Property(property: 'original_comment', type: 'string', example: null),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
            )
        ]
    )]
    public function get($id): Response {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'Comment not found'], 404);
        }

        $data = $this->jsonConverter->encodeToJson($comment, ['user']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/comments', methods: ['POST'])]
    #[OA\Post(
        path: '/api/comments',
        summary: "Créer un commentaire",
        description: "Création d'un commentaire",
        tags: ['Commentaire'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content', 'publication_id'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: "Pas moi"),
                    new OA\Property(property: 'publication_id', type: 'integer', example: 7),
                    new OA\Property(property: 'original_comment', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Commentaire créée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 4),
                        new OA\Property(property: 'content', type: 'string', example: "Wow"),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-12-03 10:47:38'),
                        new OA\Property(property: 'original_comment', type: 'string', example: 1),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: [])
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'content' and 'publication_id' required")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Introuvable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication / Comment not found')
                    ]
                )
            ),
            new OA\Response(
                response: 423,
                description: 'Publication associée verrouillée',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'This Publication is currently locked, insert a commment is not allowed')
                    ]
                )
            )
        ]
    )]
    public function insert(Request $request): Response {
        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? null;
        $publication_id = $data['publication_id'] ?? 0;
        $original_comment_id = $data['original_comment'] ?? 0;

        if (!$content || $publication_id === 0) {
            return new JsonResponse(['error' => "Parameters 'content' and 'publication_id' required"], 400);
        }

        $publication = $this->publicationRepository->find($publication_id);
        if (!$publication) {
            return new JsonResponse(['error' => "Publication not found"], 400);
        }

        $original_comment = null;
        if ($original_comment_id) {
            $original_comment = $this->commentRepository->find($original_comment_id);
            if (!$original_comment) {
                return new JsonResponse(['error' => "Comment not found"], 404);
            }
        }
        if ($publication->getIsLocked() || ($original_comment && $original_comment->getPublication()->getIsLocked())) {
            return new JsonResponse(['error' => "This Publication is currently locked, insert a commment is not allowed"], 423);
        }

        $comment = $this->commentRepository->create($content, $this->getUser(), $publication, $original_comment);
        $data = $this->jsonConverter->encodeToJson($comment, ['user']);

        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/api/comments', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/comments',
        summary: "Modifier un commentaire",
        description: "Modification d'un commentaire",
        tags: ['Commentaire'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id', 'content'],
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'content', type: 'string', example: "J'adore la seconde image")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Commentaire modifié avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'content', type: 'string', example: "J'adore la seconde image"),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-12-01 11:59:33'),
                        new OA\Property(property: 'original_comment', type: 'string', example: null),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: [])
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'id' and 'content' required")
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
                        new OA\Property(property: 'error', type: 'string', example:'You are not allowed to update this comment')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;
        $content = $data['content'] ?? null;
        if (!$id || !$content) {
            return new JsonResponse(['error' => "Parameters 'id' and 'content' required"], 400);
        }
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => "Comment not found"], 404);
        }

        $user = $this->userRepository->find($comment->getUser()->getId());
        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $userIsAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $userIsMod = in_array('ROLE_MOD', $user->getRoles());
        
        // AS = Auteur suppression | AC = Auteur commentaire
        // Si :
        // AS n’est ni modérateur ni administrateur ET elle n’est pas l’AC
        // AS est modérateur ET l’AC est modérateur ou administrateur ET ce n’est pas son propre commentaire
        if (( !($isMod || $isAdmin) && !$isCurrentUser) || ($isMod && ($userIsMod || $userIsAdmin) && !$isCurrentUser)) {
            return new JsonResponse(['error' => 'You are not allowed to update this comment'], 403);
        }

        $comment = $this->commentRepository->update($comment, $content);
        $data = $this->jsonConverter->encodeToJson($comment, ['user']);

        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/api/comments/id/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/comments/id/{id}',
        summary: "Supprimer un commentaire par son ID",
        description: "Supprimer commentaire par son ID",
        tags: ['Commentaire'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commentaire récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'content', type: 'string', example: "J'aime bien la seconde image"),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-12-01 11:59:33'),
                        new OA\Property(property: 'original_comment', type: 'string', example: null),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
                        new OA\Property(property: 'error', type: 'string', example:'You are not allowed to delete this comment')
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
            )
        ]
    )]
    public function delete($id): Response {
        if (!$id) {
            return new JsonResponse(['error' => "Parameters 'id' is required"], 400);
        }

        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => "Comment not found"], 404);
        }
        $user = $this->userRepository->find($comment->getUser()->getId());
        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $userIsAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $userIsMod = in_array('ROLE_MOD', $user->getRoles());
        
        // AS = Auteur suppression | AC = Auteur commentaire
        // Si :
        // AS n’est ni modérateur ni administrateur ET elle n’est pas l’AC
        // AS est modérateur ET l’AC est modérateur ou administrateur ET ce n’est pas son propre commentaire
        if (( !($isMod || $isAdmin) && !$isCurrentUser) || ($isMod && ($userIsMod || $userIsAdmin) && !$isCurrentUser)) {
            return new JsonResponse(['error' => 'You are not allowed to delete this comment'], 403);
        }

        $this->commentRepository->delete($comment);

        return new JsonResponse([], 200);
    }

}
