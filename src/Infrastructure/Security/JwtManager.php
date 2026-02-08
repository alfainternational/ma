<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use RuntimeException;

final class JwtManager
{
    private string $secret;
    private int $expirySeconds;

    public function __construct(?string $secret = null, int $expirySeconds = 3600)
    {
        $this->secret = $secret ?? ($_ENV['JWT_SECRET'] ?? '');

        if ($this->secret === '') {
            throw new RuntimeException('JWT_SECRET environment variable is not set');
        }

        $this->expirySeconds = $expirySeconds;
    }

    public function generateToken(string $userId, string $role): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $now = time();
        $payload = $this->base64UrlEncode(json_encode([
            'sub' => $userId,
            'role' => $role,
            'iat' => $now,
            'exp' => $now + $this->expirySeconds,
        ], JSON_THROW_ON_ERROR));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $this->secret, true)
        );

        return "{$header}.{$payload}.{$signature}";
    }

    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $this->secret, true)
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $decoded = json_decode($this->base64UrlDecode($payload), true);

        if ($decoded === null || !isset($decoded['exp'])) {
            return null;
        }

        if ($decoded['exp'] < time()) {
            return null;
        }

        return $decoded;
    }

    public function refreshToken(string $token): string
    {
        $payload = $this->validateToken($token);

        if ($payload === null) {
            throw new RuntimeException('Cannot refresh an invalid or expired token');
        }

        return $this->generateToken($payload['sub'], $payload['role']);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true) ?: '';
    }
}
