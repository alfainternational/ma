<?php
/**
 * Authentication Class
 * Marketing AI System
 */
class Auth {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register(array $data): array {
        // Validate
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if email exists
        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE email = :email",
            ['email' => $data['email']]
        );
        if ($existing) {
            return ['success' => false, 'errors' => ['البريد الإلكتروني مسجل بالفعل']];
        }

        // Hash password
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));

        // Insert user
        $userId = $this->db->insert('users', [
            'email' => $data['email'],
            'password' => $passwordHash,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'role' => 'client',
            'verification_token' => $verificationToken,
        ]);

        $this->logActivity($userId, 'register', 'تسجيل حساب جديد');

        return [
            'success' => true,
            'user_id' => $userId,
            'verification_token' => $verificationToken,
        ];
    }

    public function login(string $email, string $password): array {
        // Fetch user
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = :email AND status = 'active'",
            ['email' => $email]
        );

        if (!$user) {
            return ['success' => false, 'error' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'];
        }

        // Check lockout
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'error' => "الحساب مقفل. حاول بعد {$remaining} دقيقة"];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            $attempts = $user['login_attempts'] + 1;
            $lockUntil = null;

            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_TIME);
                $attempts = 0;
            }

            $this->db->query(
                "UPDATE users SET login_attempts = :attempts, locked_until = :lock WHERE id = :id",
                ['attempts' => $attempts, 'lock' => $lockUntil, 'id' => $user['id']]
            );

            return ['success' => false, 'error' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'];
        }

        // Reset login attempts and update last login
        $this->db->query(
            "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = :id",
            ['id' => $user['id']]
        );

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        $this->logActivity($user['id'], 'login', 'تسجيل دخول');

        return ['success' => true, 'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
        ]];
    }

    public function logout(): void {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'تسجيل خروج');
        }
        session_unset();
        session_destroy();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    public function isLoggedIn(): bool {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return false;
        }
        // Check session timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }
        return true;
    }

    public function isAdmin(): bool {
        return $this->isLoggedIn() && ($_SESSION['user_role'] ?? '') === ROLE_ADMIN;
    }

    public function isAnalyst(): bool {
        return $this->isLoggedIn() && in_array($_SESSION['user_role'] ?? '', [ROLE_ADMIN, ROLE_ANALYST]);
    }

    public function getCurrentUser(): ?array {
        if (!$this->isLoggedIn()) return null;
        return $this->db->fetch(
            "SELECT id, email, full_name, phone, company_name, role, status, created_at, last_login FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        );
    }

    public function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/public/login.php');
            exit;
        }
    }

    public function requireAdmin(): void {
        if (!$this->isAdmin()) {
            header('Location: ' . APP_URL . '/public/login.php');
            exit;
        }
    }

    public static function generateCSRFToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function validateCSRFToken(string $token): bool {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public function requestPasswordReset(string $email): array {
        $user = $this->db->fetch(
            "SELECT id FROM users WHERE email = :email AND status = 'active'",
            ['email' => $email]
        );

        // Always return success to prevent email enumeration
        if (!$user) {
            return ['success' => true, 'message' => 'إذا كان البريد مسجلاً، ستصلك رسالة لاستعادة كلمة المرور'];
        }

        $resetToken = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);

        $this->db->query(
            "UPDATE users SET reset_token = :token, reset_token_expires = :expires WHERE id = :id",
            ['token' => $resetToken, 'expires' => $expires, 'id' => $user['id']]
        );

        return [
            'success' => true,
            'message' => 'إذا كان البريد مسجلاً، ستصلك رسالة لاستعادة كلمة المرور',
            'token' => $resetToken
        ];
    }

    public function resetPassword(string $token, string $newPassword): array {
        $user = $this->db->fetch(
            "SELECT id FROM users WHERE reset_token = :token AND reset_token_expires > NOW()",
            ['token' => $token]
        );

        if (!$user) {
            return ['success' => false, 'error' => 'رابط استعادة كلمة المرور غير صالح أو منتهي'];
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

        $this->db->query(
            "UPDATE users SET password = :pass, reset_token = NULL, reset_token_expires = NULL WHERE id = :id",
            ['pass' => $passwordHash, 'id' => $user['id']]
        );

        return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح'];
    }

    private function validateRegistration(array $data): array {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'البريد الإلكتروني غير صالح';
        }
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        }
        if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
            $errors[] = 'الاسم الكامل مطلوب';
        }
        if (!empty($data['password']) && $data['password'] !== ($data['password_confirm'] ?? '')) {
            $errors[] = 'كلمة المرور وتأكيدها غير متطابقين';
        }

        return $errors;
    }

    private function logActivity(int $userId, string $action, string $description): void {
        try {
            $this->db->insert('activity_log', [
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
        } catch (Exception $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}
