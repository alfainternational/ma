<?php

namespace App\API;

use App\Config\Database;
use App\Service\AssessmentService;
use PDO;
use Exception;

/**
 * Class QuestionController
 * مسؤول عن جلب الأسئلة وحفظ الإجابات.
 */
class QuestionController {
    private PDO $db;
    private AssessmentService $assessment;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->assessment = new AssessmentService();
    }

    /**
     * حفظ إجابة سؤال واكتشاف السؤال التالي.
     */
    public function submitAnswer(string $sessionId, string $questionId, $value, ?string $userId = null): array {
        try {
            // 1. التأكد من وجود الجلسة في قاعدة البيانات (Lazy Creation)
            $stmtCheck = $this->db->prepare("SELECT id FROM assessment_sessions WHERE id = ?");
            $stmtCheck->execute([$sessionId]);
            if (!$stmtCheck->fetch()) {
                if (!$userId) throw new Exception("الجلسة غير موجودة والمستخدم غير معروف.");
                
                // جلب الشركة المرتبطة
                $stmtComp = $this->db->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
                $stmtComp->execute([$userId]);
                $companyId = $stmtComp->fetchColumn() ?: null;

                $stmtInit = $this->db->prepare("INSERT INTO assessment_sessions (id, company_id, user_id, status, current_question_id) VALUES (?, ?, ?, 'in_progress', ?)");
                $stmtInit->execute([$sessionId, $companyId, $userId, $questionId]);
            }

            $answerId = \Ramsey\Uuid\Uuid::uuid4()->toString();
            
            // Handle array values (Multi-Choice)
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            $stmt = $this->db->prepare("
                INSERT INTO answers (id, session_id, question_id, answer_value) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE answer_value = VALUES(answer_value)
            ");
            $stmt->execute([$answerId, $sessionId, $questionId, $value]);

            // التحقق من السؤال الذي يليه لتحديث الجلسة
            $flow = new \App\Service\QuestionFlowService();
            // جلب الإجابات الحالية (مبسط)
            $stmtAnswers = $this->db->prepare("SELECT question_id, answer_value FROM answers WHERE session_id = ?");
            $stmtAnswers->execute([$sessionId]);
            $answers = $stmtAnswers->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $nextId = $flow->getNextQuestionId($questionId, $answers, []); // السياق فارغ حالياً

            // تحديث وقت النشاط والسؤال الحالي
            $stmtUpdate = $this->db->prepare("
                UPDATE assessment_sessions 
                SET updated_at = NOW(), 
                    current_question_id = ?,
                    progress_percent = (SELECT COUNT(*) * 100 / (SELECT COUNT(*) FROM questions WHERE active = 1) FROM answers WHERE session_id = ?)
                WHERE id = ?
            ");
            $stmtUpdate->execute([$nextId, $sessionId, $sessionId]);

            return [
                'status' => 'success',
                'message' => 'تم حفظ الإجابة بنجاح',
                'next_id' => $nextId
            ];
        } catch (Exception $e) {
            error_log("SubmitAnswer Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * جلب بيانات سؤال معين باللغتين.
     */
    public function getQuestion(?string $questionId = null): array {
        if (empty($questionId)) {
            // جلب أول سؤال متاح
            $stmt = $this->db->query("SELECT * FROM questions ORDER BY display_order ASC, id ASC LIMIT 1");
        } else {
            $stmt = $this->db->prepare("SELECT * FROM questions WHERE id = ?");
            $stmt->execute([$questionId]);
        }
        
        $question = $stmt->fetch();

        return $question ? $question : ['status' => 'error', 'message' => 'السؤال غير موجود'];
    }
    /**
     * تجهيز بيانات بدء التقييم (SSR)
     */
    public function handleAssessmentStart(?string $sessionId = null, ?string $userId = null): array {
        $currentQuestionId = null;

        // 1. إنشاء أو اعتماد جلسة
        if (!$sessionId) {
            $sessionId = \Ramsey\Uuid\Uuid::uuid4()->toString();
            
            if ($userId) {
                // البحث عن شركة هذا المستخدم
                $stmt = $this->db->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
                $stmt->execute([$userId]);
                $companyId = $stmt->fetchColumn() ?: null;
                
                $firstQuestion = $this->db->query("SELECT id FROM questions WHERE active = 1 ORDER BY display_order ASC LIMIT 1")->fetchColumn();
                $stmt = $this->db->prepare("INSERT INTO assessment_sessions (id, company_id, user_id, status, current_question_id) VALUES (?, ?, ?, 'in_progress', ?)");
                $stmt->execute([$sessionId, $companyId, $userId, $firstQuestion]);
            }
        } else {
            // استكمال جلسة موجودة - جلب السؤال الحالي من الجلسة
            $stmt = $this->db->prepare("SELECT current_question_id FROM assessment_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            $currentQuestionId = $stmt->fetchColumn();
        }

        // 2. جلب السؤال المطلوب
        $question = $this->getQuestion($currentQuestionId);

        return [
            'sessionId' => $sessionId,
            'initialQuestion' => $question
        ];
    }
}
