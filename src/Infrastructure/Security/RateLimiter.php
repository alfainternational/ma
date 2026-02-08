<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class RateLimiter
{
    private string $storagePath;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? sys_get_temp_dir() . '/rate_limiter';

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function check(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        return $this->getRemainingAttempts($key, $maxAttempts, $windowSeconds) > 0;
    }

    public function getRemainingAttempts(string $key, int $maxAttempts, int $windowSeconds): int
    {
        $file = $this->getFilePath($key);
        $attempts = $this->loadAttempts($file, $windowSeconds);

        return max(0, $maxAttempts - count($attempts));
    }

    public function hit(string $key): void
    {
        $file = $this->getFilePath($key);
        $attempts = $this->loadAttempts($file, PHP_INT_MAX);
        $attempts[] = time();

        file_put_contents($file, json_encode($attempts, JSON_THROW_ON_ERROR), LOCK_EX);
    }

    public function reset(string $key): void
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            unlink($file);
        }
    }

    private function getFilePath(string $key): string
    {
        return $this->storagePath . '/' . md5($key) . '.json';
    }

    private function loadAttempts(string $file, int $windowSeconds): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (!is_array($data)) {
            return [];
        }

        $cutoff = time() - $windowSeconds;

        return array_values(array_filter($data, fn(int $ts): bool => $ts > $cutoff));
    }
}
