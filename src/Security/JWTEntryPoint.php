<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class JWTEntryPoint implements AuthenticationEntryPointInterface {

    public function start(Request $request, ?AuthenticationException $authException = null): JsonResponse {
        return new JsonResponse(['error' => 'Missing token'], 401);
    }

}
