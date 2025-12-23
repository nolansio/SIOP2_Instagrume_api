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
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use OpenApi\Attributes as OA;

class CommentController extends AbstractController
{

    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly JsonConverter $jsonConverter,
        private readonly UserRepository $userRepository,
        private readonly TagAwareCacheInterface $cacheComments
    ) {}

    #[Route('/api/comments', methods: ['GET'])]
    #[OA\Get(
        path: '/api/comments',
        summary: "Récupérer tout les commentaires",
        description: "Récupération de tout les commentaires avec pagination",
        tags: ['Commentaire'],
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
                description: 'Commentaires récupérés avec succès',
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

        $cacheKey = "comments_page_{$page}_limit_{$limit}";

        $result = $this->cacheComments->get($cacheKey, function (ItemInterface $item) use ($page, $limit) {
            $item->expiresAfter(120); // 2 minutes
            $item->tag(['comments']);
            return $this->commentRepository->findPaginated($page, $limit);
        });

        $data = $this->jsonConverter->encodeToJson($result['data'], ['user']);

        return new JsonResponse($data, Response::HTTP_OK, [
            'X-Total-Count' => $result['total'],
            'X-Total-Pages' => $result['pages'],
            'X-Current-Page' => $result['current_page'],
            'X-Per-Page' => $result['per_page']
        ], true);
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
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
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
    public function get(int $id): JsonResponse
    {
        if (!($comment = $this->commentRepository->findOneByIdOptimized($id))) {
            return $this->json(['error' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->jsonConverter->encodeToJson($comment, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
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
                required: ['id', 'content'],
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 7),
                    new OA\Property(property: 'content', type: 'string', example: "Pas moi"),
                    new OA\Property(property: 'original_comment', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Commentaire créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 4),
                        new OA\Property(property: 'content', type: 'string', example: "Pas moi"),
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
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'id' and 'content' required")
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
                description: 'Publication verrouillée',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication is locked')
                    ]
                )
            )
        ]
    )]
    public function insert(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!($content = $data['content'] ?? null) || !($id = $data['id'] ?? null)) {
            return $this->json(['error' => "Parameters 'id' and 'content' required"], Response::HTTP_BAD_REQUEST);
        }

        if (!($publication = $this->publicationRepository->find($id))) {
            return $this->json(['error' => "Publication not found"], Response::HTTP_NOT_FOUND);
        }

        $original_comment = null;
        if ($original_comment_id = $data['original_comment'] ?? null) {
            if (!($original_comment = $this->commentRepository->find($original_comment_id))) {
                return $this->json(['error' => "Comment not found"], Response::HTTP_NOT_FOUND);
            }
        }

        if ($publication->isLocked() || ($original_comment && $original_comment->getPublication()->isLocked())) {
            return $this->json(['error' => "Publication is locked"], Response::HTTP_LOCKED);
        }

        $comment = $this->commentRepository->create($content, $this->getUser(), $publication, $original_comment);

        $this->cacheComments->invalidateTags(['comments']);

        $data = $this->jsonConverter->encodeToJson($comment, ['user']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
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
                response: 200,
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to update this comment')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!($id = $data['id'] ?? null) || !($content = $data['content'] ?? null)) {
            return $this->json(['error' => "Parameters 'id' and 'content' required"], Response::HTTP_BAD_REQUEST);
        }

        if (!($comment = $this->commentRepository->find($id))) {
            return $this->json(['error' => "Comment not found"], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModifyComment($comment)) {
            return $this->json(['error' => 'You are not allowed to update this comment'], Response::HTTP_FORBIDDEN);
        }

        $comment = $this->commentRepository->update($comment, $content);

        $this->cacheComments->invalidateTags(['comments']);

        $data = $this->jsonConverter->encodeToJson($comment, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/comments/id/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/comments/id/{id}',
        summary: "Supprimer un commentaire par son ID",
        description: "Suppression d'un commentaire par son ID",
        tags: ['Commentaire'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commentaire supprimé avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to delete this comment')
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
    public function delete(int $id): JsonResponse
    {
        if (!($comment = $this->commentRepository->find($id))) {
            return $this->json(['error' => "Comment not found"], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModifyComment($comment)) {
            return $this->json(['error' => 'You are not allowed to delete this comment'], Response::HTTP_FORBIDDEN);
        }

        $this->commentRepository->delete($comment);

        $this->cacheComments->invalidateTags(['comments']);

        return $this->json([], Response::HTTP_OK);
    }

    private function canModifyComment(Comment $comment): bool
    {
        $currentUser = $this->getUser();
        $commentAuthor = $comment->getUser();

        $isCurrentUser = $currentUser->getUserIdentifier() === $commentAuthor->getUserIdentifier();

        $currentUserRoles = $currentUser->getRoles();
        $authorRoles = $commentAuthor->getRoles();

        $isMod = in_array('ROLE_MOD', $currentUserRoles);
        $isAdmin = in_array('ROLE_ADMIN', $currentUserRoles);
        $authorIsMod = in_array('ROLE_MOD', $authorRoles);
        $authorIsAdmin = in_array('ROLE_ADMIN', $authorRoles);

        if ($isCurrentUser) {
            return true;
        }

        if ($isAdmin) {
            return true;
        }

        if ($isMod && !$authorIsMod && !$authorIsAdmin) {
            return true;
        }

        return false;
    }
}
