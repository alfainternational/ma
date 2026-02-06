<?php

namespace App\Core\Engines;

/**
 * Class ScoringEngine
 * محرك التقييم (Scoring Engine).
 * مسؤول عن حساب درجات النضج الرقمي، المالي، الاستراتيجي، والمخاطر.
 */
class ScoringEngine {
    
    /**
     * حساب الدرجة النهائية لقطاع معين.
     * @param array $answers الإجابات ذات الصلة بالقطاع
     * @param array $weights أوزان الأسئلة (من بنك الأسئلة)
     * @return float درجة من 100
     */
    public function calculateCategoryScore(array $answers, array $weights): float {
        $totalWeight = 0;
        $earnedScore = 0;

        foreach ($weights as $questionId => $weight) {
            if (isset($answers[$questionId])) {
                $totalWeight += $weight;
                $earnedScore += $this->evaluateAnswer($answers[$questionId]) * $weight;
            }
        }

        return ($totalWeight > 0) ? ($earnedScore / $totalWeight) * 100 : 0;
    }

    /**
     * تقييم جودة الإجابة (0.00 إلى 1.00).
     */
    private function evaluateAnswer($answer): float {
        // إذا كانت الإجابة "نعم"، فهي غالباً 1.00
        if (trim(strtolower($answer)) === 'yes') return 1.0;
        
        // إذا كانت "لا"، فهي غالباً 0.00
        if (trim(strtolower($answer)) === 'no') return 0.0;

        // منطق إضافي للقيم الرقمية أو الخيارات المتعددة
        if (is_numeric($answer)) {
            // منطق مخصص للنسب المئوية مثلاً
            return min(1.0, (float)$answer / 100);
        }

        return 0.5; // قيمة افتراضية للإجابات الأخرى
    }

    /**
     * حساب "درجة الاستعجال" (Urgency Score) بناءً على سياق الجلسة.
     */
    public function calculateUrgency(array $context): int {
        $urgency = $context['urgency'] ?? 'medium';
        return match($urgency) {
            'critical' => 10,
            'high' => 8,
            'medium' => 5,
            'low' => 2,
            default => 5,
        };
    }
}
