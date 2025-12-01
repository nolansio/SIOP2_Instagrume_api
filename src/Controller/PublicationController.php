<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class PublicationController extends AbstractController {

    private PublicationRepository $publicationRepository;
    private JsonConverter $jsonConverter;

    public function __construct(PublicationRepository $publicationRepository, JsonConverter $jsonConverter) {
        $this->publicationRepository = $publicationRepository;
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/api/publications', methods: ['GET'])]
    #[OA\Get(
        path: '/api/publications',
        summary: "Récupère toutes les publications",
        description: "Récupération de toutes les publications",
        tags: ['Publication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Publications récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'description', type: 'string', example: 'Je cultive moi-même mes légumes'),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-11-27 12:06:32'),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: [])
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
        $data = $this->jsonConverter->encodeToJson($publications, ['public']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/publications/id/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/publications/id/{id}',
        summary: "Récupère une publication par son ID",
        description: "Récupération d'une publication par son ID",
        tags: ['Publication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Publication récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'description', type: 'string', example: 'Je cultive moi-même mes légumes'),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025-11-27 12:06:32'),
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'likes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'dislikes', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'user', type: 'array', items: new OA\Items(type: 'object'), example: []),
                        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(type: 'object'), example: [])
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

        $data = $this->jsonConverter->encodeToJson($publication, ['public']);

        return new JsonResponse($data, 200, [], true);
    }

    // TODO : POST PUT DELETE

}
