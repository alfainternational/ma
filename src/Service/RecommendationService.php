<?php

namespace App\Service;

/**
 * Class RecommendationService
 * محرك التوصيات (Recommendation Engine).
 * مسؤول عن توليد التوصيات في 3 طبقات: استراتيجية، تكتيكية، وتنفيذية.
 */
class RecommendationService {
    
    /**
     * توليد باقة توصيات كاملة بناءً على نتائج التحليل.
     * @param array $analysisResults النتائج القادمة من InferenceEngine
     * @return array توصيات مرتبة حسب الأولوية والطبقة
     */
    public function generateRecommendations(array $analysisResults): array {
        $recommendations = [
            'strategic' => [], // طويلة المدى (6-12 شهر)
            'tactical' => [],  // متوسطة المدى (3-6 أشهر)
            'execution' => []  // فورية (أسبوع - شهر)
        ];

        // 1. استخراج التوصيات من الخبراء
        foreach ($analysisResults['expert_insights'] as $insight) {
            if ($insight['type'] === 'missing_service_opportunity') {
                $recommendations['strategic'][] = [
                    'title' => "تأسيس {$insight['service']}",
                    'benefit' => $insight['importance'],
                    'priority' => 'high'
                ];
            }
        }

        // 2. منطق التنبيهات الحرجة -> تحويلها لتوصيات تنفيذية فورية
        foreach ($analysisResults['critical_alerts'] as $alert) {
            $recommendations['execution'][] = [
                'title' => "معالجة خطأ: {$alert['title']}",
                'action' => $alert['recommendation'],
                'priority' => 'critical'
            ];
        }

        return $recommendations;
    }
}
