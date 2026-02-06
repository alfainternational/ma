<?php

namespace App\API;

use App\Config\Database;
use Firebase\JWT\JWT;
use PDO;

/**
 * Class AuthController
 * مسؤول عن عمليات التسجيل، تسجيل الدخول، وإصدار رموز JWT.
 */
class AuthController {
    private PDO $db;
    private string $key;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->key = $_ENV['JWT_SECRET'] ?? 'default_secret';
    }

    /**
     * تسجيل الدخول وإصدار رمز JWT.
     */
    public function login(string $email, string $password): array {
        $stmt = $this->db->prepare("SELECT id, password_hash, full_name, role FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $payload = [
                'iss' => $_ENV['APP_URL'],
                'aud' => $_ENV['APP_URL'],
                'iat' => time(),
                'nba' => time(),
                'exp' => time() + (60 * 60 * 24), // يوم واحد
                'data' => [
                    'userId' => $user['id'],
                    'role' => $user['role']
                ]
            ];

            $jwt = JWT::encode($payload, $this->key, 'HS256');

            return [
                'status' => 'success',
                'token' => $jwt,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ];
        }

        return ['status' => 'error', 'message' => 'بيانات الدخول غير صحيحة'];
    }
    /**
     * تسجيل مستخدم جديد.
     */
    public function register(string $fullName, string $email, string $password, string $phone): array {
        // التحقق من وجود البريد
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['status' => 'error', 'message' => 'البريد الإلكتروني مسجل مسبقاً'];
        }

        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->db->prepare("INSERT INTO users (id, full_name, email, password_hash, phone, role, status) VALUES (?, ?, ?, ?, ?, 'client', 'active')");
            $stmt->execute([$uuid, $fullName, $email, $hash, $phone]);

            // Auto-login after registration
            return $this->login($email, $password);
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => 'حدث خطأ أثناء التسجيل: ' . $e->getMessage()];
        }
    }
}
