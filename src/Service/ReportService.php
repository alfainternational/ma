<?php

namespace App\Service;

use App\Config\Database;
use PDO;

/**
 * Class ReportService
 * مسؤول عن تجميع النتائج وبناء هيكل التقرير النهائي.
 */
class ReportService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * بناء محتوى التقرير بشكل هيكلي.
     */
    public function buildReportData(string $sessionId): array {
        // جلب نتائج التحليل
        $stmt = $this->db->prepare("SELECT * FROM analysis_results WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $analysis = $stmt->fetchAll();

        $report = [
            'header' => [
                'title' => 'تقرير التحليل التسويقي الاستراتيجي',
                'date' => date('Y-m-d'),
                'company' => $this->getCompanyName($sessionId)
            ],
            'sections' => []
        ];

        // إضافة قسم خطة 2026 الخاصة (إذا وجدت بياناتها)
        if ($this->hasStrategicData($sessionId)) {
            $report['sections'][] = [
                'title' => 'خطة الاستجابة السريعة 2026',
                'type' => 'strategic_plan_2026',
                'content' => $this->generateStrategicPlan($sessionId)
            ];
        }

        // تحويل نتائج الخبراء إلى أقسام في التقرير
        foreach ($analysis as $row) {
            $scores = json_decode($row['scores'], true);
            $insights = json_decode($row['insights'], true);
            
            $report['sections'][] = [
                'title' => $this->getSectionTitle($row['analysis_type']),
                'score' => $scores['maturity'] ?? $scores['strategy_maturity'] ?? 0,
                'insights' => $insights
            ];
        }

        return $report;
    }

    private function hasStrategicData(string $sessionId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM answers WHERE session_id = ? AND question_id LIKE 'Q_2026%'");
        $stmt->execute([$sessionId]);
        return $stmt->fetchColumn() > 0;
    }

    private function generateStrategicPlan(string $sessionId): array {
        // جلب إجابات 2026
        $stmt = $this->db->prepare("SELECT question_id, answer_value FROM answers WHERE session_id = ? AND question_id LIKE 'Q_2026%'");
        $stmt->execute([$sessionId]);
        $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'goal_2026' => $data['Q_2026_BASIC_004'] ?? '',
            'main_challenge' => $data['Q_2026_BASIC_005'] ?? '',
            'offer' => [
                'outcome' => $data['Q_2026_OFFER_001'] ?? '',
                'mechanism' => $data['Q_2026_OFFER_002'] ?? ''
            ],
            'channels' => $data['Q_2026_CHANNELS_001'] ?? [],
            'funnel' => [
                'attract' => $data['Q_2026_FUNNEL_001'] ?? ''
            ],
            'action_plan_30_days' => [
                'week_1' => 'تحليل عميق لعناصر العرض والتحقق من الـ Proof.',
                'week_2' => 'تجهيز القنوات المختارة (إنستغرام/تيك توك) بالمحتوى الاستراتيجي.',
                'week_3' => 'إطلاق حملة الجذب التجريبية لاختبار مسار التحويل.',
                'week_4' => 'تحليل البيانات الأولى وتعديل رسالة السطر الواحد.'
            ]
        ];
    }

    private function getCompanyName($sessionId): string {
        $stmt = $this->db->prepare("
            SELECT c.name FROM companies c 
            JOIN assessment_sessions s ON c.id = s.company_id 
            WHERE s.id = ?
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchColumn() ?: 'عميلنا العزيز';
    }

    private function getSectionTitle($type): string {
        return match($type) {
            'strategic' => 'الرؤية والاستراتيجية',
            'financial' => 'الأداء المالي والربحية',
            'digital' => 'النضج التسويقي الرقمي',
            'market' => 'تحليل السوق والمنافسة',
            default => 'تحليل فني'
        };
    }
}
