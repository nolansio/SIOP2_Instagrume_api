<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\User;
use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
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
                        new OA\Property(property: 'error', type: 'string', example: 'Missing token / Invalid token')
                    ]
                )
            )
        ]
    )]
    public function getAll(): Response {
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
            )
        ]
    )]
    public function get($id): Response {
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
                            new OA\Property(
                                property: 'images',
                                type: 'array',
                                items: new OA\Items(type: 'string', format: 'binary')
                            )
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
    public function insert(Request $request): Response {
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
        summary: "Modifier la description d'une publication",
        description: "Modifier la description d'une publication",
        tags: ['Publication'],
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
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: Publication::class,
            example: [
                "id" => 7,
                "description"=> "Abra exemple"
            ]
        )
    )]
    public function update(Request $request, ManagerRegistry $doctrine): JsonResponse {
        $json = $request->getContent();
        $data = json_decode($json, true);
        if (!empty($data["description"]) && !empty($data["id"])) {
            $entityManager = $doctrine->getManager();
            $description = $data["description"];
            $id = $data["id"];
            $publication = $this->publicationRepository->find($id);
            if (!$publication) {
                return new JsonResponse(["error" =>"publication not found"], 404);
            }

            $user = $this->userRepository->find($publication->user_id);
            $currentUser = $this->getUser();
            $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
            // Si :
            // L'Auteur de la modification n'est pas l'Auteur de la publication
            if (!$isCurrentUser) {
                return new JsonResponse(['error' => 'You are not allowed to update this publication'], 403);
            }


            $publication->setDescription($description);
            $entityManager->persist($publication);
            $entityManager->flush();
            return new JsonResponse($this->jsonConverter->encodeToJson($publication), 200, [], true);
        } else {
            return new JsonResponse(["error" =>"bad fields"], 422);
        }
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
                        new OA\Property(property: 'error', type: 'string', example: "'Parameters 'id' is required'")
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
    public function delete($id): Response {
        if (!$id) {
            return new JsonResponse(['error' => "Parameters 'id' is required"], 400);
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
