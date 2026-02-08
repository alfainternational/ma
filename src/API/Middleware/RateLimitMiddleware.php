<?php
declare(strict_types=1);

namespace App\API\Middleware;

use App\Infrastructure\Security\RateLimiter;

class RateLimitMiddleware
{
    public function __construct(
        private RateLimiter $limiter,
        private int $maxAttempts = 60,
        private int $windowSeconds = 60
    ) {}

    public function handle(): bool
    {
        $key = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!$this->limiter->check($key, $this->maxAttempts, $this->windowSeconds)) {
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            header('Retry-After: ' . $this->windowSeconds);
            echo json_encode([
                'success' => false,
                'data' => null,
                'message' => 'تم تجاوز الحد المسموح من الطلبات. حاول مرة أخرى لاحقاً.',
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }

        return true;
    }
}
