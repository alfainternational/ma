<?php
declare(strict_types=1);

namespace App\API\Middleware;

class CorsMiddleware
{
    private array $allowedOrigins;
    private array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    private array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'X-CSRF-Token'];

    public function __construct(?array $allowedOrigins = null)
    {
        $this->allowedOrigins = $allowedOrigins ?? [
            $_ENV['APP_URL'] ?? 'http://localhost:3000',
            'http://localhost:5173',
        ];
    }

    public function handle(): bool
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $this->allowedOrigins, true) || in_array('*', $this->allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            return false;
        }

        return true;
    }
}
