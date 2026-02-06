<?php

namespace App\Core\Experts;

/**
 * Class RiskManager
 * مدير المخاطر (Risk Manager).
 * مسؤول عن اكتشاف التهديدات المالية، القانونية، والسوقية قبل وقوعها.
 */
class RiskManager extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $scores = ['risk_mitigation' => 0];

        $hasInsurance = ($answers['has_business_insurance'] ?? 'no') === 'yes';

        // تحليل إدارة المخاطر
        if (!$hasInsurance) {
            $insights[] = $this->generateMissingServiceInsight(
                'تأمين المسؤولية المهنية والتجاري',
                'العمل بدون تأمين يضع كامل ثروتك الشخصية وتعب السنوات تحت رحمة حادث واحد أو دعوى قضائية غير متوقعة.',
                [
                    'تغطية تكاليف الحوادث أو الأخطاء المهنية.',
                    'توفير راحة البال للتركيز على النمو.',
                    'تحسين مصداقية الشركة أمام العملاء والمستثمرين.'
                ]
            );
        }

        $scores['risk_score'] = $hasInsurance ? 2 : 8; // مقياس من 10 (عالي هو سيئ)
        $scores['strategy_maturity'] = $hasInsurance ? 60 : 40;

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'summary' => 'إدارة المخاطر ليست تكلفة، بل هي صمام أمان لاستمرارية العمل.'
        ];
    }
}
