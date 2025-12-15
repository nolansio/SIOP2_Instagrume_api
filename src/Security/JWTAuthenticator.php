<?php

namespace App\Security;

use App\Service\JWTService;
use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JWTAuthenticator extends AbstractAuthenticator {

    private JWTService $jwtManager;
    private UserRepository $userRepository;

    public function __construct(JWTService $jwtManager, UserRepository $userRepository) {
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): ?bool {
        return str_starts_with($request->getPathInfo(), '/api') && $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport {
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        try {
            $payload = $this->jwtManager->decodeToken($token);
            if ($payload == null) {
                throw new AuthenticationException('Invalid token');
            }
            // Vérification du bannisemment de l'utilisateur en bdd
            $user = $this->userRepository->findOneByUsername($payload['username']);
            if ($user === null || $user->isBanned()) {
                throw new AuthenticationException('Invalid token');
            }
        } catch (Exception) {
            throw new AuthenticationException('Invalid token');
        }
        return new SelfValidatingPassport(new UserBadge($payload['username']));
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?JsonResponse {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse {
        return new JsonResponse(['error' => $exception->getMessage()], 401);
    }

}
