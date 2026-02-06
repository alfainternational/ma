<?php
namespace App\API;

use App\Config\Database;
use PDO;

class ClientController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getDashboardData(string $userId): array {
        // Fetch User's Assessment Sessions
        $stmt = $this->db->prepare("
            SELECT s.id, s.started_at, s.status,
            (SELECT COUNT(*) FROM answers a WHERE a.session_id = s.id) as answer_count
            FROM assessment_sessions s 
            WHERE s.user_id = ? 
            ORDER BY s.started_at DESC
        ");
        $stmt->execute([$userId]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reports as &$report) {
            $report['status_label'] = ($report['status'] === 'completed') ? 'مكتمل' : 'قيد التنفيذ';
            $report['status_class'] = ($report['status'] === 'completed') ? 'success' : 'warning';
            
            // Fetch actual progress from DB instead of dummy calculation
            $stmtProgress = $this->db->prepare("SELECT progress_percent FROM assessment_sessions WHERE id = ?");
            $stmtProgress->execute([$report['id']]);
            $actualProgress = $stmtProgress->fetchColumn();
            
            $report['score'] = ($report['status'] === 'completed') ? '85%' : ($actualProgress ?? 0) . '%';
        }

        return [
            'reports' => $reports
        ];
    }
    public function createCompany(string $userId, array $data): array {
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO companies 
                (id, user_id, name, sector, founded_year, employee_count, contact_info) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $contactInfo = json_encode(['website' => $data['website'] ?? '']);
            
            $stmt->execute([
                $uuid, 
                $userId, 
                $data['name'], 
                $data['sector'], 
                !empty($data['founded_year']) ? $data['founded_year'] : null, 
                !empty($data['employee_count']) ? $data['employee_count'] : null,
                $contactInfo
            ]);

            return ['status' => 'success', 'company_id' => $uuid];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => 'فشل إنشاء ملف الشركة: ' . $e->getMessage()];
        }
    }
    public function getCompanyId(string $userId): ?string {
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: null;
    }
}
