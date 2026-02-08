<?php

namespace App\Service;

/**
 * Class AlertService
 * محرك التنبيهات (Alert System).
 * مسؤول عن رصد الأخطاء، التناقضات، والفرص الضائعة.
 */
class AlertService {
    
    /**
     * رصد التناقضات بين الإجابات (Contradiction Detection).
     * مثال: العميل يقول "إيراداتي مرتفعة" ولكن "لا يمكنني دفع رواتب الموظفين".
     */
    public function detectContradictions(array $answers): array {
        $contradictions = [];

        // مثال بسيط: إيرادات عالية + لا يوجد نمو
        if (isset($answers['revenue_level']) && $answers['revenue_level'] === 'high' && 
            isset($answers['revenue_trend']) && $answers['revenue_trend'] === 'declining') {
            $contradictions[] = [
                'id' => 'ALERT_CONT_001',
                'title' => 'تناقض في تقييم الإيرادات',
                'message' => 'لقد قيمت الإيرادات بأنها مرتفعة، ولكنك ذكرت أنها في تراجع. هذا يشير إلى خطر في استدامة الأرباح.',
                'severity' => 'warning'
            ];
        }

        return $contradictions;
    }
}
