<?php

namespace App\Core\Experts;

/**
 * Class MarketAnalyst
 * خبير تحليل السوق (Market Analyst).
 * مسؤول عن تحليل القطاع، المنافسين، والاتجاهات السوقية.
 */
class MarketAnalyst extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $scores = ['market_position' => 0];

        $competitorCount = (int)($answers['competitor_count'] ?? 0);
        $marketShare = (float)($answers['current_market_share'] ?? 0);

        // تحليل المنافسة
        if ($competitorCount > 10) {
            $insights[] = [
                'type' => 'market_insight',
                'title' => 'تشبع السوق بالمنافسين',
                'text' => 'يعمل نشاطك في سوق عالي التنافسية، مما يتطلب تميزاً (Differentiation) واضحاً جداً لجذب انتباه العملاء.',
                'severity' => 'high'
            ];
        }

        // متطلب "تحليل الفوائد المفقودة" - تحليل المنافسين
        if (!isset($answers['conduct_regular_competitor_audit']) || $answers['conduct_regular_competitor_audit'] === 'no') {
            $insights[] = $this->generateMissingServiceInsight(
                'نظام مراقبة المنافسين',
                'عدم مراقبة حركة المنافسين يجعلك في وضع رد الفعل بدلاً من المبادرة، وقد تفقد حصصاً سوقية دون علمك.',
                [
                    'التعرف على فجوات السوق التي يتركها المنافسون.',
                    'تعديل استراتيجية التسعير بناءً على تحركات السوق.',
                    'اكتشاف الاتجاهات الجديدة قبل انتشارها.'
                ]
            );
        }

        // حساب درجة الوضع السوقي
        $scores['market_position'] = ($marketShare > 5) ? 80 : 40;

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'summary' => 'تحليل المنافسة يشير إلى الحاجة لخلق ميزة تنافسية فريدة.'
        ];
    }
}
