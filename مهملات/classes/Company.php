<?php
/**
 * Company Model
 * Marketing AI System
 */
class Company {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int {
        return $this->db->insert('companies', [
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'sector' => $data['sector'],
            'years_in_business' => $data['years_in_business'] ?? null,
            'employee_count' => $data['employee_count'] ?? null,
            'annual_revenue' => $data['annual_revenue'] ?? null,
            'monthly_costs' => $data['monthly_costs'] ?? null,
            'location' => isset($data['location']) ? json_encode($data['location']) : null,
            'contact_info' => isset($data['contact_info']) ? json_encode($data['contact_info']) : null,
            'description' => $data['description'] ?? null,
        ]);
    }

    public function getById(int $id): ?array {
        $company = $this->db->fetch("SELECT * FROM companies WHERE id = :id", ['id' => $id]);
        if ($company) {
            $company['location'] = json_decode($company['location'], true);
            $company['contact_info'] = json_decode($company['contact_info'], true);
        }
        return $company;
    }

    public function getByUserId(int $userId): array {
        $companies = $this->db->fetchAll(
            "SELECT * FROM companies WHERE user_id = :uid ORDER BY created_at DESC",
            ['uid' => $userId]
        );
        foreach ($companies as &$c) {
            $c['location'] = json_decode($c['location'], true);
            $c['contact_info'] = json_decode($c['contact_info'], true);
            $c['sector_name_ar'] = SECTORS[$c['sector']]['ar'] ?? $c['sector'];
        }
        return $companies;
    }

    public function update(int $id, array $data): bool {
        $allowed = ['name', 'sector', 'years_in_business', 'employee_count', 'annual_revenue', 'monthly_costs', 'description'];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (isset($data['location'])) {
            $filtered['location'] = json_encode($data['location']);
        }
        if (isset($data['contact_info'])) {
            $filtered['contact_info'] = json_encode($data['contact_info']);
        }

        if (empty($filtered)) return false;
        return $this->db->update('companies', $filtered, 'id = :id', ['id' => $id]) > 0;
    }

    public function delete(int $id): bool {
        return $this->db->delete('companies', 'id = :id', ['id' => $id]) > 0;
    }

    public function getAll(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT c.*, u.full_name as owner_name
             FROM companies c JOIN users u ON c.user_id = u.id
             ORDER BY c.created_at DESC LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalCount(): int {
        return $this->db->count('companies');
    }

    public function getSectorDistribution(): array {
        return $this->db->fetchAll(
            "SELECT sector, COUNT(*) as count FROM companies GROUP BY sector ORDER BY count DESC"
        );
    }

    public function belongsToUser(int $companyId, int $userId): bool {
        $result = $this->db->fetch(
            "SELECT id FROM companies WHERE id = :cid AND user_id = :uid",
            ['cid' => $companyId, 'uid' => $userId]
        );
        return $result !== null;
    }
}
