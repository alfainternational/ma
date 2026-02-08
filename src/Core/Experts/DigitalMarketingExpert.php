<?php

namespace App\Core\Experts;

/**
 * Class DigitalMarketingExpert
 * خبير التسويق الرقمي (Digital Marketing Expert).
 * مسؤول عن تحليل القنوات الرقمية، التواجد الاجتماعي، والإعلانات الممولة.
 */
class DigitalMarketingExpert extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $scores = ['digital_maturity' => 0];

        $hasAds = ($answers['run_paid_ads'] ?? 'no') === 'yes';
        $socialEngagement = (float)($answers['social_engagement_rate'] ?? 0);

        // تحليل النضج الرقمي
        if (!$hasAds) {
            $insights[] = [
                'type' => 'digital_opportunity',
                'title' => 'اعتماد الإعلانات الممولة',
                'text' => 'الاعتماد الكلي على الوصول الطبيعي (Organic) أصبح محدوداً جداً؛ نوصي بالبدء بحملات محدودة لاختبار الجمهور المستهدف.',
                'severity' => 'medium'
            ];
        }

        // متطلب "تحليل الفوائد المفقودة" - نظام بيكسل / تتبع
        if (!isset($answers['use_tracking_pixel']) || $answers['use_tracking_pixel'] === 'no') {
            $insights[] = $this->generateMissingServiceInsight(
                'أدوات تتبع التحويل (Pixel)',
                'بدون أدوات التتبع، أنت تنفق الميزانية الإعلانية في "صندوق أسود" دون معرفة العائد الحقيقي على كل ريال مصروف.',
                [
                    'إمكانية إعادة استهداف (Retargeting) الزوار المهتمين.',
                    'تحسين الحملات بناءً على البيانات الحقيقية وليس التوقعات.',
                    'تقليل تكلفة اكتساب العميل بشكل كبير.'
                ]
            );
        }

        // درجة النضج الرقمي
        $score = 0;
        if (($answers['has_website'] ?? 'no') === 'yes') $score += 30;
        if ($hasAds) $score += 30;
        if ($socialEngagement > 2) $score += 40;
        
        $scores['digital_maturity'] = $score;

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'summary' => 'التواجد الرقمي يحتاج لربط أدوات التتبع المتقدمة لتعظيم العائد.'
        ];
    }
}
