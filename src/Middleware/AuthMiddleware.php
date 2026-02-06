<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * Class AuthMiddleware
 * مسؤول عن التحقق من رمز JWT قبل السماح بالوصول لمسارات الـ API.
 */
class AuthMiddleware {
    private string $key;

    public function __construct() {
        $this->key = $_ENV['JWT_SECRET'] ?? 'default_secret';
    }

    /**
     * التحقق من الصلاحية من خلال ترويسة Authorization.
     */
    public function handle(): ?array {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        // Fallback for Apache/FastCGI where header might be in $_SERVER
        if (!$authHeader) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        }

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بالوصول - يرجى تسجيل الدخول']);
            exit;
        }

        try {
            $jwt = $matches[1];
            $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
            return (array)$decoded->data;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'رمز الدخول منتهي الصلاحية أو غير صحيح']);
            exit;
        }
    }
}
