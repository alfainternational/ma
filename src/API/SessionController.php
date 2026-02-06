<?php

namespace App\API;

use App\Config\Database;
use App\Service\QuestionFlowService;
use PDO;
use Exception;

/**
 * Class SessionController
 * مسؤول عن إدارة جلسات التقييم (بدء جلسة، استرجاع التقدم).
 */
class SessionController {
    private PDO $db;
    private QuestionFlowService $flow;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->flow = new QuestionFlowService();
    }

    /**
     * بدء جلسة تقييم جديدة.
     */
    public function startSession(string $companyId, string $userId): array {
        $sessionId = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        try {
            $stmt = $this->db->prepare("INSERT INTO assessment_sessions (id, company_id, user_id, status) VALUES (?, ?, ?, 'in_progress')");
            $stmt->execute([$sessionId, $companyId, $userId]);

            // الحصول على السؤال الأول عبر محرك الأسئلة التكيفي
            $firstQuestionId = $this->flow->getNextQuestionId(null, [], []);

            return [
                'status' => 'success',
                'session_id' => $sessionId,
                'first_question_id' => $firstQuestionId
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
