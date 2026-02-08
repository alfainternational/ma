<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Application\Services\AuthService;

class AuthController
{
    public function __construct(private AuthService $authService) {}

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $result = $this->authService->login(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );

            $this->json(true, $result, 'تم تسجيل الدخول بنجاح');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 401);
        }
    }

    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $user = $this->authService->register($data);
            $this->json(true, $user->toArray(), 'تم إنشاء الحساب بنجاح', 201);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 422);
        }
    }

    public function refreshToken(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $result = $this->authService->refreshToken($data['refresh_token'] ?? '');
            $this->json(true, $result, 'تم تجديد التوكن بنجاح');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 401);
        }
    }

    public function me(): void
    {
        $userId = $_REQUEST['user_id'] ?? '';
        try {
            $token = $this->authService->validateToken(
                str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '')
            );
            $this->json(true, $token, 'بيانات المستخدم');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 401);
        }
    }

    public function logout(): void
    {
        $userId = $_REQUEST['user_id'] ?? '';
        $this->authService->logout($userId);
        $this->json(true, null, 'تم تسجيل الخروج بنجاح');
    }

    private function json(bool $success, mixed $data, string $message, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);
    }
}
