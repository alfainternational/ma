<?php
/**
 * User Model
 * Marketing AI System
 */
class User {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getById(int $id): ?array {
        return $this->db->fetch(
            "SELECT id, email, full_name, phone, company_name, role, status, email_verified, created_at, last_login, metadata
             FROM users WHERE id = :id",
            ['id' => $id]
        );
    }

    public function getByEmail(string $email): ?array {
        return $this->db->fetch(
            "SELECT id, email, full_name, phone, company_name, role, status
             FROM users WHERE email = :email",
            ['email' => $email]
        );
    }

    public function getAll(int $limit = 50, int $offset = 0, string $role = ''): array {
        $sql = "SELECT id, email, full_name, phone, company_name, role, status, created_at, last_login FROM users";
        $params = [];

        if ($role) {
            $sql .= " WHERE role = :role";
            $params['role'] = $role;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        if ($role) $stmt->bindValue(':role', $role);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['full_name', 'phone', 'company_name', 'status', 'role'];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) return false;

        return $this->db->update('users', $filtered, 'id = :id', ['id' => $id]) > 0;
    }

    public function updatePassword(int $id, string $currentPassword, string $newPassword): array {
        $user = $this->db->fetch("SELECT password FROM users WHERE id = :id", ['id' => $id]);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'كلمة المرور الحالية غير صحيحة'];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        $this->db->query("UPDATE users SET password = :pass WHERE id = :id", ['pass' => $hash, 'id' => $id]);

        return ['success' => true, 'message' => 'تم تحديث كلمة المرور بنجاح'];
    }

    public function delete(int $id): bool {
        return $this->db->delete('users', 'id = :id', ['id' => $id]) > 0;
    }

    public function getTotalCount(string $role = ''): int {
        if ($role) {
            return $this->db->count('users', 'role = :role', ['role' => $role]);
        }
        return $this->db->count('users');
    }

    public function getRecentUsers(int $limit = 5): array {
        return $this->db->fetchAll(
            "SELECT id, email, full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT {$limit}"
        );
    }
}
