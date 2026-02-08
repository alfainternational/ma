<?php

namespace App\Core\Experts;

/**
 * Class ChiefStrategist
 * الخبير الاستراتيجي الأول (Chief Strategist).
 * مسؤول عن الرؤية الشاملة، اتجاه النمو، وتحليل SWOT.
 */
class ChiefStrategist extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $recommendations = [];
        $scores = ['strategy_maturity' => 0];

        // مثال على منطق تحليل الفوائد المفقودة (الموقع الإلكتروني كمثال)
        // تحليل هدف 2026
        if (isset($answers['Q_2026_BASIC_004'])) {
            $goal = $answers['Q_2026_BASIC_004'];
            $insights[] = [
                'type' => 'strategic_goal_analysis',
                'expert' => 'كبير الاستراتيجيين',
                'title' => 'تحليل هدف 2026',
                'content' => "هدفك المتمثل في '{$goal}' يتطلب مواءمة دقيقة للموارد. سنعمل على تحويله إلى خطوات قابلة للتنفيذ شهرياً.",
                'severity' => 'info'
            ];
        }

        // تحليل أكبر تحدي (عنق الزجاجة)
        if (isset($answers['Q_2026_BASIC_005'])) {
            $challenge = $answers['Q_2026_BASIC_005'];
            $insights[] = [
                'type' => 'bottleneck_identification',
                'expert' => 'كبير الاستراتيجيين',
                'title' => 'تحديد عنق الزجاجة',
                'content' => "التحدي المرتبط بـ '{$challenge}' هو العائق الأكبر حالياً. خطتنا للـ 30 يوماً القادمة ستستهدف حل هذا العائق أولاً.",
                'severity' => 'high'
            ];
        }

        if (isset($answers['has_website']) && $answers['has_website'] === 'no') {
            $insights[] = $this->generateMissingServiceInsight(
                'الموقع الإلكتروني الاحترافي',
                'يعتبر الموقع الإلكتروني هو واجهة الشركة الرقمية ومركز عمليات التسويق الحديث.',
                [
                    'الوصول لعملاء جدد على مدار الساعة.',
                    'بناء هوية رقمية موثوقة بعيداً عن منصات التواصل الاجتماعي.',
                    'إمكانية استخدام أدوات التحليل المتقدمة لفهم سلوك الزوار.'
                ]
            );
        }

        // تحليل SWOT مبدئي بناءً على الإجابات
        $swot = [
            'strengths' => [],
            'weaknesses' => [],
            'opportunities' => [],
            'threats' => []
        ];

        // منطق SWOT الحقيقي (تحليل الكلمات المفتاحية والسياق)
        $textAnswers = implode(" ", $answers); // دمج كل النصوص للتحليل العام

        // نقاط القوة
        if (strpos($textAnswers, 'خبرة') !== false || strpos($textAnswers, 'نمو') !== false || ($answers['years_in_business'] ?? 0) > 3) {
            $swot['strengths'][] = 'أساس تشغيلي قوي وخبرة سوقية.';
        }
        if (isset($answers['has_website']) && $answers['has_website'] === 'yes') {
            $swot['strengths'][] = 'تواجد رقمي مؤسس يمكن البناء عليه.';
        }

        // نقاط الضعف
        if (isset($answers['Q_2026_BASIC_005'])) { // التحدي الرئيسي
            $swot['weaknesses'][] = "تحدي تشغيلي رئيسي: " . $answers['Q_2026_BASIC_005'];
        }
        if (!isset($answers['marketing_budget']) || $answers['marketing_budget'] < 1000) {
            $swot['weaknesses'][] = 'محدودية الموارد المالية المخصصة للتسويق.';
        }

        // الفرص
        if (strpos($textAnswers, 'توسع') !== false || strpos($textAnswers, 'جديد') !== false) {
            $swot['opportunities'][] = 'إمكانية التوسع في أسواق أو منتجات جديدة.';
        }
        $swot['opportunities'][] = 'التحول نحو الأتمتة لتقليل التكاليف التشغيلية.';

        // التهديدات
        if (strpos($textAnswers, 'منافس') !== false || strpos($textAnswers, 'سعر') !== false) {
            $swot['threats'][] = 'تزايد حدة المنافسة وحرب الأسعار المحتملة.';
        }

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'swot' => $swot,
            'priority_recommendation' => 'تطوير خارطة طريق استراتيجية للتحول الرقمي.'
        ];
    }
}
