<?php

namespace App\Core\Experts;

/**
 * Class ConsumerPsychologist
 * خبير علم نفس المستهلك (Consumer Psychologist).
 * مسؤول عن تحليل سلوك العميل، رحلة الشراء، والدوافع النفسية.
 */
class ConsumerPsychologist extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $scores = ['customer_centricity' => 0];

        $understandsPainPoints = ($answers['understand_customer_pain_points'] ?? 'no') === 'yes';

        // تحليل سيكولوجية العميل
        if (!$understandsPainPoints) {
            $insights[] = [
                'type' => 'customer_insight',
                'title' => 'فجوة في فهم دوافع العميل',
                'text' => 'التركيز على ميزات المنتج بدلاً من حل مشاكل العميل يقلل من معدلات التحول بشكل كبير.',
                'severity' => 'high'
            ];
        }

        // متطلب "تحليل الفوائد المفقودة" - خرائط رحلة العميل
        if (!isset($answers['has_customer_journey_map']) || $answers['has_customer_journey_map'] === 'no') {
            $insights[] = $this->generateMissingServiceInsight(
                'خرائط رحلة العميل (Customer Journey Maps)',
                'بدون خريطة واضحة، أنت تخمن نقاط الاحتكاك التي يواجهها عميلك بدلاً من معالجتها علمياً.',
                [
                    'تحديد اللحظات الحاسمة التي يقرر فيها العميل الشراء أو المغادرة.',
                    'تحسين تجربة العميل في كل نقطة تلامس (Touchpoint).',
                    'زيادة ولاء العملاء (Retention) وتقليل معدل الانقطاع (Churn).'
                ]
            );
        }

        $scores['customer_centricity'] = $understandsPainPoints ? 80 : 30;

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'summary' => 'التركيز على نقاط ألم العميل هو المفتاح لزيادة المبيعات.'
        ];
    }
}
