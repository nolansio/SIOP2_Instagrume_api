<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class PublicationController extends AbstractController {

    private PublicationRepository $publicationRepository;
    private JsonConverter $jsonConverter;
    private userRepository $userRepository;

    public function __construct(PublicationRepository $publicationRepository, JsonConverter $jsonConverter, UserRepository $userRepository) {
        $this->publicationRepository = $publicationRepository;
        $this->jsonConverter = $jsonConverter;
        $this->userRepository = $userRepository;
    }

    #[Route('/api/publications', methods: ['GET'])]
    #[OA\Get(
        path: '/api/publications',
        summary: "Récupérer toutes les publications",
        description: "Récupération de toutes les publications",
        tags: ['Publication'],
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
    public function getAll(): JsonResponse {
        $publications = $this->publicationRepository->findAll();

        $data = $this->jsonConverter->encodeToJson($publications, ['publication']);
        return new JsonResponse($data, 200, [], true);
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
    public function get($id): JsonResponse {
        $publication = $this->publicationRepository->find($id);

        if (!$publication) {
            return new JsonResponse(['error' => 'Publication not found'], 404);
        }

        $data = $this->jsonConverter->encodeToJson($publication, ['publication']);
        return new JsonResponse($data, 200, [], true);
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
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'username' and 'password' required")
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
    public function insert(Request $request): JsonResponse {
        $description = $request->request->get('description');
        $images = $request->files->get('images', []);

        if (!$images) {
            $images = [];
        } elseif (!is_array($images)) {
            $images = [$images];
        }

        if (!$description && empty($images)) {
            return new JsonResponse(['error' => "Parameters 'description' and 'images' required"], 400);
        }

        $ImagesDirectory = $this->getParameter('kernel.project_dir').'/public/images';
        $imagePaths = [];

        foreach ($images as $file) {
            $name = uniqid().'.png';
            $file->move($ImagesDirectory, $name);

            $imagePaths[] = '/images/'.$name;
        }

        $publication = $this->publicationRepository->create($description, $this->getUser(), $imagePaths);
        $data = $this->jsonConverter->encodeToJson($publication, ['publication']);

        return new JsonResponse($data, 201, [], true);
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
                response: 201,
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
    public function update(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;
        $description = $data['description'] ?? null;

        if (!$id || !$description) {
            return new JsonResponse(['error' => "Parameters 'id' and 'description' required"], 400);
        }

        $publication = $this->publicationRepository->find($id);
        if (!$publication) {
            return new JsonResponse(['error' =>'Publication not found'], 404);
        }

        $currentUser = $this->getUser();
        $isPublicationUser = $currentUser->getUserIdentifier() === $publication->getUser()->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles()) || in_array('ROLE_ADMIN', $currentUser->getRoles());

        if (!$isPublicationUser && !$isMod) {
            return new JsonResponse(['error' => 'You are not allowed to update this publication'], 403);
        }

        $this->publicationRepository->update($publication, $description);

        $data = $this->jsonConverter->encodeToJson($publication, ['user']);
        return new JsonResponse($data, 200, [], true);
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
                description: 'Publication supprimé avec succès',
                content: new OA\JsonContent(
                    properties: []
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Mauvaise requête',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: "Parameters 'id' required")
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
    public function delete($id): JsonResponse {
        if (!$id) {
            return new JsonResponse(['error' => "Parameters 'id' required"], 400);
        }

        $publication = $this->publicationRepository->find($id);
        if (!$publication) {
            return new JsonResponse(['error' => "Publication not found"], 404);
        }

        $user = $this->userRepository->find($publication->user_id);
        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $userIsAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $userIsMod = in_array('ROLE_MOD', $user->getRoles());

        // AS = Auteur suppression | AP = Auteur publication
        // Si :
        // AS n’est ni modérateur ni administrateur ET elle n’est pas l’AC
        // AS est modérateur ET l’AC est modérateur ou administrateur ET ce n’est pas son propre publication
        if (( !($isMod || $isAdmin) && !$isCurrentUser) || ($isMod && ($userIsMod || $userIsAdmin) && !$isCurrentUser)) {
            return new JsonResponse(['error' => 'You are not allowed to delete this publication'], 403);
        }

        $this->publicationRepository->delete($publication);

        return new JsonResponse([], 200);
    }

}
