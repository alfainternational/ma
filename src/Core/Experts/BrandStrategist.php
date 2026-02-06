<?php

namespace App\Core\Experts;

/**
 * Class BrandStrategist
 * خبير استراتيجية العلامة التجارية (Brand Strategist).
 * مسؤول عن الهوية، الرسالة، وصوت العلامة التجارية.
 */
class BrandStrategist extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $scores = ['brand_equity' => 0];

        // تحليل رسالة السطر الواحد
        if (isset($answers['Q_2026_MSG_001'])) {
            $message = $answers['Q_2026_MSG_001'];
            $isSimple = strlen($message) < 150;
            
            $insights[] = [
                'type' => 'brand_messaging_analysis',
                'expert' => 'خبير العلامة التجارية',
                'title' => 'تحليل الرسالة التسويقية',
                'content' => $isSimple 
                    ? "رسالتك: '{$message}' ممتازة وبسيطة. الوضوح هو مفتاح الجذب."
                    : "رسالتك: '{$message}' تبدو معقدة قليلاً. حاول تبسيطها ليفهمها طفل في العاشرة كما في النموذج.",
                'severity' => $isSimple ? 'success' : 'warning'
            ];
        }

        $hasBrandIdentity = ($answers['has_visual_identity_guide'] ?? 'no') === 'yes';

        // تحليل هوية العلامة
        if (!$hasBrandIdentity) {
            $insights[] = $this->generateMissingServiceInsight(
                'دليل الهوية البصرية المتكامل',
                'عدم وجود دليل هوية يؤدي لتشتت الانطباع لدى العميل وضعف تمييزك بين المنافسين.',
                [
                    'توحيد المظهر العام في جميع القنوات (أوفلاين وأونلاين).',
                    'بناء ثقة أسرع مع الجمهور المستهدف.',
                    'تسهيل مهمة صانعي المحتوى والمصممين في تمثيل العلامة بشكل صحيح.'
                ]
            );
        }

        $scores['brand_equity'] = $hasBrandIdentity ? 75 : 30;
        $scores['digital_maturity'] = $hasBrandIdentity ? 50 : 20; // يؤثر على النضج الرقمي أيضاً

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'summary' => 'بناء هوية بصرية قوية هو الخطوة الأولى لترسيخ العلامة في ذهن العميل.'
        ];
    }
}
