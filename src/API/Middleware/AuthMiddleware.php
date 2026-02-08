<?php
declare(strict_types=1);

namespace App\API\Middleware;

use App\Infrastructure\Security\JwtManager;

class AuthMiddleware
{
    public function __construct(private JwtManager $jwt) {}

    public function handle(): bool
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            $this->unauthorized('التوكن مطلوب');
            return false;
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwt->validateToken($token);

        if ($payload === null) {
            $this->unauthorized('توكن غير صالح أو منتهي الصلاحية');
            return false;
        }

        $_REQUEST['user_id'] = $payload['sub'] ?? '';
        $_REQUEST['user_role'] = $payload['role'] ?? 'user';
        $_REQUEST['user_email'] = $payload['email'] ?? '';

        return true;
    }

    public function requireRole(string ...$roles): bool
    {
        $userRole = $_REQUEST['user_role'] ?? '';
        if (!in_array($userRole, $roles, true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'data' => null,
                'message' => 'غير مصرح لك بهذا الإجراء',
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }
        return true;
    }

    private function unauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'data' => null,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);
    }
}
