<?php

namespace App\Service;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService {
    private string $privateKey;
    private string $publicKey;

    public function __construct(string $privateKeyPath, string $publicKeyPath) {
        $this->privateKey = file_get_contents($privateKeyPath);
        $this->publicKey = file_get_contents($publicKeyPath);
    }

    public function encodeToken(array $payload, int $expiration = 3600): string {
        $payload['exp'] = time() + $expiration;
        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    public function decodeToken(string $token): ?array {
        try {
            return (array) JWT::decode($token, new Key($this->publicKey, 'RS256'));
        } catch (Exception) {
            return null;
        }
    }

}
