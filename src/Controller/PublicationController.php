<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use OpenApi\Attributes as OA;

class PublicationController extends AbstractController
{

    public function __construct(
        private readonly PublicationRepository $publicationRepository,
        private readonly JsonConverter $jsonConverter,
        private readonly UserRepository $userRepository,
        private readonly TagAwareCacheInterface $cachePublications
    ) {}

    #[Route('/api/publications', methods: ['GET'])]
    #[OA\Get(
        path: '/api/publications',
        summary: "Récupérer toutes les publications",
        description: "Récupération de toutes les publications avec pagination",
        tags: ['Publication'],
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
                schema: new OA\Schema(type: 'integer', default: 20, example: 20)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Publications récupérées avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
                    ]
                )
            )
        ]
    )]
    public function getAll(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

        $cacheKey = "publications_page_{$page}_limit_{$limit}";

        $result = $this->cachePublications->get($cacheKey, function (ItemInterface $item) use ($page, $limit) {
            $item->expiresAfter(300);
            $item->tag(['publications']);
            return $this->publicationRepository->findPaginated($page, $limit);
        });

        $data = $this->jsonConverter->encodeToJson($result['data'], ['publication']);

        return new JsonResponse($data, Response::HTTP_OK, [
            'X-Total-Count' => $result['total'],
            'X-Total-Pages' => $result['pages'],
            'X-Current-Page' => $result['current_page'],
            'X-Per-Page' => $result['per_page']
        ], true);
    }

    #[Route('/api/publications/id/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/publications/id/{id}',
        summary: "Récupérer une publication par son ID",
        description: "Récupération d'une publication par son ID",
        tags: ['Publication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Publication récupérée avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid token')
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
    public function get(int $id): JsonResponse
    {
        if (!($publication = $this->publicationRepository->findOneByIdOptimized($id))) {
            return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->jsonConverter->encodeToJson($publication, ['publication']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/publications', methods: ['POST'])]
    #[OA\Post(
        path: '/api/publications',
        summary: "Créer une publication",
        description: "Création d'une publication",
        tags: ['Publication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'multipart/form-data' => new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['description', 'images'],
                        properties: [
                            new OA\Property(property: 'description', type: 'string', example: 'Cultivation de mes plantes'),
                            new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string', format: 'binary'))
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Publication créée avec succès',
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
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'description' and 'images' required")
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
    public function insert(Request $request): JsonResponse
    {
        $description = $request->request->get('description');
        $images = $request->files->get('images', []);

        $images = match (true) {
            empty($images) => [],
            is_array($images) => $images,
            default => [$images]
        };

        if (!$description && empty($images)) {
            return $this->json(['error' => "Parameters 'description' and 'images' required"], Response::HTTP_BAD_REQUEST);
        }

        $imagePaths = [];
        if (!empty($images)) {
            $imagesDirectory = $this->getParameter('kernel.project_dir') . '/public/images';

            foreach ($images as $file) {
                $filename = uniqid() . '.png';
                $file->move($imagesDirectory, $filename);
                $imagePaths[] = '/images/' . $filename;
            }
        }

        $publication = $this->publicationRepository->create($this->getUser(), $description, $imagePaths);

        $this->cachePublications->invalidateTags(['publications']);

        $data = $this->jsonConverter->encodeToJson($publication, ['publication']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/publications', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/publications',
        summary: "Modifier une publication",
        description: "Modification d'une publication",
        tags: ['Publication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id', 'description'],
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'description', type: 'string', example: 'Cultivation de mes plantes !')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Publication modifiée avec succès',
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
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'id' and 'description' required")
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to update this publication')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!($id = $data['id'] ?? null) || !($description = $data['description'] ?? null)) {
            return $this->json(['error' => "Parameters 'id' and 'description' required"], Response::HTTP_BAD_REQUEST);
        }

        if (!($publication = $this->publicationRepository->find($id))) {
            return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModifyPublication($publication)) {
            return $this->json(['error' => 'You are not allowed to update this publication'], Response::HTTP_FORBIDDEN);
        }

        $publication = $this->publicationRepository->update($publication, $description);

        $this->cachePublications->invalidateTags(['publications']);

        $data = $this->jsonConverter->encodeToJson($publication, ['user']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/api/publications/id/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/publications/id/{id}',
        summary: "Supprimer une publication",
        description: "Suppression d'une publication",
        tags: ['Publication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Publication supprimée avec succès',
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to delete this publication')
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
    public function delete(int $id): JsonResponse
    {
        if (!($publication = $this->publicationRepository->find($id))) {
            return $this->json(['error' => "Publication not found"], Response::HTTP_NOT_FOUND);
        }

        if (!$this->canModifyPublication($publication)) {
            return $this->json(['error' => 'You are not allowed to delete this publication'], Response::HTTP_FORBIDDEN);
        }

        $this->publicationRepository->delete($publication);

        $this->cachePublications->invalidateTags(['publications']);

        return $this->json([], Response::HTTP_OK);
    }

    private function canModifyPublication(Publication $publication): bool
    {
        $currentUser = $this->getUser();
        $publicationAuthor = $publication->getUser();

        $isAuthor = $currentUser->getUserIdentifier() === $publicationAuthor->getUserIdentifier();

        $currentUserRoles = $currentUser->getRoles();
        $authorRoles = $publicationAuthor->getRoles();

        $isMod = in_array('ROLE_MOD', $currentUserRoles);
        $isAdmin = in_array('ROLE_ADMIN', $currentUserRoles);
        $authorIsMod = in_array('ROLE_MOD', $authorRoles);
        $authorIsAdmin = in_array('ROLE_ADMIN', $authorRoles);

        if ($isAuthor) {
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
