<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PublicationRepository;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\JsonConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Doctrine\Persistence\ManagerRegistry;


class ModerationController extends AbstractController {

    public function __construct(private UserRepository $userRepository, private JsonConverter $jsonConverter, private ImageRepository $imageRepository, private PublicationRepository $publicationRepository) {
    }

    #[Route('/api/users/ban', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/ban',
        summary: "Bannissement d'un utilisateur",
        description: "Verrouiller un utilisateur",
        tags: ['Moderation'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'int', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Bannissement : Utilisateur verrouillé',
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
                        new OA\Property(property: 'error', type: 'string', example: "Parameter 'user_id' required")
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
                        new OA\Property(property: 'error', type: 'string', example:'You are not allowed to ban this user')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Not Found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function ban(Request $request): Response {
        
        $json = $request->getContent();
        $data = json_decode($json, true);
        $id = $data["user_id"];
        if (!$id) {
            return new JsonResponse(['error' => "Parameter 'user_id' required"], 400);
        }
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 409);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $userIsAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $userIsMod = in_array('ROLE_MOD', $user->getRoles());

        if ((!$isMod && !$isAdmin) || ($isCurrentUser) || ($isMod && ($userIsMod || $userIsAdmin)) || ($userIsAdmin)) {
            return new JsonResponse(['error' => 'You are not allowed to ban this user'], 403);
        }

        $this->userRepository->updateIsBan($user, true);
        $data = $this->jsonConverter->encodeToJson($user, ['user', 'user_private']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users/deban', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/deban',
        summary: "Débannissement d'un utilisateur",
        description: "Déverrouiller un utilisateur",
        tags: ['Moderation'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'int', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Débannissement : Utilisateur déverrouillé',
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
                        new OA\Property(property: 'error', type: 'string', example: "Parameter 'user_id' required")
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to deban this user')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Not Found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function deban(Request $request): Response {
        
        $json = $request->getContent();
        $data = json_decode($json, true);
        $id = $data["user_id"];
        if (!$id) {
            return new JsonResponse(['error' => "Parameter 'user_id' required"], 400);
        }
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => "User not found"], 409);
        }

        $currentUser = $this->getUser();
        $isCurrentUser = $currentUser->getUserIdentifier() == $user->getUserIdentifier();
        $isMod = in_array('ROLE_MOD', $currentUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $userIsAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $userIsMod = in_array('ROLE_MOD', $user->getRoles());

        if ((!$isMod && !$isAdmin) || ($isCurrentUser) || ($isMod && ($userIsMod || $userIsAdmin))) {
            return new JsonResponse(['error' => 'You are not allowed to deban this user'], 403);
        }

        $this->userRepository->updateIsBan($user, false);
        $data = $this->jsonConverter->encodeToJson($user, ['user', 'user_private']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/publications/lock', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/publications/lock',
        summary: "Vérouillage d'une publication",
        description: "Empêche la création de nouveaux commentaires sur cette publication",
        tags: ['Moderation'],
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
                description: 'Vérouillage : cette Publication ne pourra avoir de nouveaux commentaires',
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
                        new OA\Property(property: 'is_lock', type: 'boolean', example: true)
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to lock this publication')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Not Found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication not found')
                    ]
                )
            )
        ]
    )]
    public function lock(Request $request): Response {
        
        $json = $request->getContent();
        $data = json_decode($json, true);
        $id = $data["publication_id"];
        if (!$id) {
            return new JsonResponse(['error' => "Parameter 'publication_id' required"], 400);
        }
        $publication = $this->publicationRepository->find($id);
        if (!$publication) {
            return new JsonResponse(['error' => "Publication not found"], 409);
        }

        $currentUser = $this->getUser();
        $isMod = count(array_intersect($currentUser->getRoles(), ['ROLE_MOD', 'ROLE_ADMIN'])) > 0;

        if (!$isMod) {
            return new JsonResponse(['error' => 'You are not allowed to lock this publication'], 403);
        }
        $this->publicationRepository->updateIsLock($publication, true);
        $data = $this->jsonConverter->encodeToJson($publication, ['publication', 'publication_private']);
        return new JsonResponse($data, 200, [], true);
    }


    #[Route('/api/publications/delock', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/publications/delock',
        summary: "Dévérouillage d'une publication",
        description: "Réautorise la création de nouveaux commentaires sur cette publication",
        tags: ['Moderation'],
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
                description: 'Dévérouillage : cette Publication pourra de nouveau être commententé',
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
                        new OA\Property(property: 'is_lock', type: 'boolean', example: false)
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
                        new OA\Property(property: 'error', type: 'string', example: 'You are not allowed to delock this publication')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Not Found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Publication not found')
                    ]
                )
            )
        ]
    )]
    public function delock(Request $request): Response {
        
        $json = $request->getContent();
        $data = json_decode($json, true);
        $id = $data["publication_id"];
        if (!$id) {
            return new JsonResponse(['error' => "Parameter 'publication_id' required"], 400);
        }
        $publication = $this->publicationRepository->find($id);
        if (!$publication) {
            return new JsonResponse(['error' => "Publication not found"], 409);
        }

        $currentUser = $this->getUser();
        $isMod = count(array_intersect($currentUser->getRoles(), ['ROLE_MOD', 'ROLE_ADMIN'])) > 0;

        if (!$isMod) {
            return new JsonResponse(['error' => 'You are not allowed to delock this publication'], 403);
        }
        $this->publicationRepository->updateIsLock($publication, false);
        $data = $this->jsonConverter->encodeToJson($publication, ['publication', 'publication_private']);
        return new JsonResponse($data, 200, [], true);
    }
}