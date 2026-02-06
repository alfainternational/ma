<?php
/**
 * Assessment Session Model
 * Marketing AI System
 */
class AssessmentSession {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, int $companyId, string $type = 'full_assessment'): array {
        $uuid = $this->generateUUID();

        $sessionId = $this->db->insert('assessment_sessions', [
            'session_uuid' => $uuid,
            'user_id' => $userId,
            'company_id' => $companyId,
            'session_type' => $type,
            'status' => 'in_progress',
            'context' => json_encode([
                'business_stage' => null,
                'urgency_level' => null,
                'budget_tier' => null,
                'sector' => null,
                'digital_maturity_initial' => null,
            ]),
        ]);

        return [
            'id' => $sessionId,
            'session_uuid' => $uuid,
        ];
    }

    public function getById(int $id): ?array {
        $session = $this->db->fetch(
            "SELECT s.*, c.name as company_name, c.sector as company_sector, u.full_name as user_name
             FROM assessment_sessions s
             JOIN companies c ON s.company_id = c.id
             JOIN users u ON s.user_id = u.id
             WHERE s.id = :id",
            ['id' => $id]
        );
        if ($session) {
            $session['context'] = json_decode($session['context'], true);
            $session['metadata'] = json_decode($session['metadata'] ?? 'null', true);
        }
        return $session;
    }

    public function getByUUID(string $uuid): ?array {
        $session = $this->db->fetch(
            "SELECT s.*, c.name as company_name, c.sector as company_sector
             FROM assessment_sessions s
             JOIN companies c ON s.company_id = c.id
             WHERE s.session_uuid = :uuid",
            ['uuid' => $uuid]
        );
        if ($session) {
            $session['context'] = json_decode($session['context'], true);
        }
        return $session;
    }

    public function getByUserId(int $userId, int $limit = 20): array {
        $sessions = $this->db->fetchAll(
            "SELECT s.*, c.name as company_name, c.sector as company_sector
             FROM assessment_sessions s
             JOIN companies c ON s.company_id = c.id
             WHERE s.user_id = :uid
             ORDER BY s.started_at DESC LIMIT {$limit}",
            ['uid' => $userId]
        );
        foreach ($sessions as &$s) {
            $s['context'] = json_decode($s['context'], true);
        }
        return $sessions;
    }

    public function updateProgress(int $sessionId, int $answeredCount, int $totalCount, ?string $currentQuestionId = null): void {
        $progress = $totalCount > 0 ? round(($answeredCount / $totalCount) * 100) : 0;

        $data = [
            'progress_percent' => $progress,
            'answered_questions' => $answeredCount,
            'total_questions' => $totalCount,
        ];
        if ($currentQuestionId) {
            $data['current_question_id'] = $currentQuestionId;
        }

        $this->db->update('assessment_sessions', $data, 'id = :id', ['id' => $sessionId]);
    }

    public function updateContext(int $sessionId, array $context): void {
        $this->db->query(
            "UPDATE assessment_sessions SET context = :ctx WHERE id = :id",
            ['ctx' => json_encode($context), 'id' => $sessionId]
        );
    }

    public function complete(int $sessionId): void {
        $this->db->update('assessment_sessions', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'progress_percent' => 100,
        ], 'id = :id', ['id' => $sessionId]);
    }

    public function abandon(int $sessionId): void {
        $this->db->update('assessment_sessions', [
            'status' => 'abandoned',
        ], 'id = :id', ['id' => $sessionId]);
    }

    public function getAll(int $limit = 50, int $offset = 0, string $status = ''): array {
        $sql = "SELECT s.*, c.name as company_name, u.full_name as user_name
                FROM assessment_sessions s
                JOIN companies c ON s.company_id = c.id
                JOIN users u ON s.user_id = u.id";
        $params = [];

        if ($status) {
            $sql .= " WHERE s.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY s.started_at DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, $params);
    }

    public function getStats(): array {
        $total = $this->db->count('assessment_sessions');
        $completed = $this->db->count('assessment_sessions', "status = 'completed'");
        $inProgress = $this->db->count('assessment_sessions', "status = 'in_progress'");
        $abandoned = $this->db->count('assessment_sessions', "status = 'abandoned'");

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'abandoned' => $abandoned,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    public function belongsToUser(int $sessionId, int $userId): bool {
        $result = $this->db->fetch(
            "SELECT id FROM assessment_sessions WHERE id = :sid AND user_id = :uid",
            ['sid' => $sessionId, 'uid' => $userId]
        );
        return $result !== null;
    }

    private function generateUUID(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
