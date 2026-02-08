<?php

declare(strict_types=1);

namespace App\Shared\Utils;

use InvalidArgumentException;

final class UUID
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public static function generate(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(substr($bytes, 0, 4)),
            bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)),
            bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6))
        );
    }

    public static function toBin(string $uuid): string
    {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUID: {$uuid}");
        }

        return hex2bin(str_replace('-', '', $uuid));
    }

    public static function fromBin(string $bin): string
    {
        if (strlen($bin) !== 16) {
            throw new InvalidArgumentException('Binary UUID must be exactly 16 bytes');
        }

        $hex = bin2hex($bin);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    public static function isValid(string $uuid): bool
    {
        return (bool) preg_match(self::PATTERN, $uuid);
    }
}
