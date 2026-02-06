<?php

namespace App\Service;

use App\Core\Engines\InferenceEngine;
use App\Core\Engines\ScoringEngine;
use App\Config\Database;

/**
 * Class AssessmentService
 * الواجهة الأساسية (Facade) لإدارة عملية التقييم بالكامل.
 */
class AssessmentService {
    private InferenceEngine $inference;
    private ScoringEngine $scoring;
    private RecommendationService $recommendation;
    private AlertService $alert;

    public function __construct() {
        $this->inference = new InferenceEngine();
        $this->scoring = new ScoringEngine();
        $this->recommendation = new RecommendationService();
        $this->alert = new AlertService();
    }

    /**
     * معالجة نتائج جلسة تقييم كاملة.
     * @param string $sessionId معرف الجلسة في قاعدة البيانات
     * @return array النتائج النهائية للتحليل
     */
    public function processSession(string $sessionId): array {
        // 1. جلب الإجابات من قاعدة البيانات
        $answers = $this->fetchAnswers($sessionId);
        
        // 2. جلب السياق (القطاع، الأهداف)
        $context = $this->fetchContext($sessionId);

        // 3. تشغيل محرك الاستنتاج (الخبراء الـ 10)
        $analysis = $this->inference->runInference($answers, $context);

        // 4. رصد التناقضات الإضافية
        $contradictions = $this->alert->detectContradictions($answers);
        $analysis['critical_alerts'] = array_merge($analysis['critical_alerts'], $contradictions);

        // 5. توليد التوصيات النهائية
        $recommendations = $this->recommendation->generateRecommendations($analysis);
        $analysis['strategic_recommendations'] = $recommendations;

        // 6. حفظ النتائج في قاعدة البيانات
        $this->saveResults($sessionId, $analysis);

        return $analysis;
    }

    private function fetchAnswers(string $sessionId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT question_id, answer_value FROM answers WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $rows = $stmt->fetchAll();
        
        $answers = [];
        foreach ($rows as $row) {
            $answers[$row['question_id']] = $row['answer_value'];
        }
        return $answers;
    }

    private function fetchContext(string $sessionId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT sector FROM companies c JOIN assessment_sessions s ON c.id = s.company_id WHERE s.id = ?");
        $stmt->execute([$sessionId]);
        return ['sector' => $stmt->fetchColumn() ?: 'general'];
    }

    private function saveResults(string $sessionId, array $results): void {
        $db = Database::getConnection();
        $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        
        $stmt = $db->prepare("
            INSERT INTO analysis_results (id, session_id, analysis_type, scores, insights, recommendations) 
            VALUES (?, ?, 'comprehensive', ?, ?, ?)
        ");
        
        $stmt->execute([
            $id,
            $sessionId,
            json_encode($results['scores']),
            json_encode($results['expert_insights']),
            json_encode($results['strategic_recommendations'])
        ]);
    }
}
