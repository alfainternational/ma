<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class PasswordHasher
{
    private const COST = 12;

    public function hash(string $password): string
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => self::COST]);

        if ($hashed === false) {
            throw new \RuntimeException('Failed to hash password');
        }

        return $hashed;
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => self::COST]);
    }
}
