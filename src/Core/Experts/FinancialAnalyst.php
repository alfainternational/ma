<?php

namespace App\Core\Experts;

/**
 * Class FinancialAnalyst
 * الخبير المالي (Financial Analyst).
 * مسؤول عن تحليل الإيرادات، التكاليف، ميزانية التسويق، وصحة نموذج العمل المالي.
 */
class FinancialAnalyst extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $alerts = [];
        $scores = ['financial_health' => 0];

        $revenue = (float)($answers['annual_revenue'] ?? 0);
        $marketingBudget = (float)($answers['marketing_budget'] ?? 0);

        // تحليل ميزانية التسويق من الإيرادات (قاعدة Alert_CRIT_002)
        if ($revenue > 0) {
            $budgetRatio = $marketingBudget / $revenue;
            if ($budgetRatio > 0.50) {
                $alerts[] = [
                    'id' => 'ALERT_CRIT_002',
                    'type' => 'critical',
                    'title' => 'ميزانية غير مستدامة',
                    'message' => 'الميزانية التسويقية تتجاوز 50% من الإيرادات - هذا النموذج غير قابل للاستمرار مالياً.',
                    'recommendation' => 'تخفيض فوري في الإنفاق أو إعادة هيكلة قنوات الاستحواذ.'
                ];
            }
        }

        // متطلب "تحليل الفوائد المفقودة" - نظام تتبع مالي
        if (!isset($answers['use_accounting_software']) || $answers['use_accounting_software'] === 'no') {
            $insights[] = $this->generateMissingServiceInsight(
                'نظام محاسبي سحابي',
                'المحاسبة اليدوية تزيد من نسبة الأخطاء وتحرمك من الرؤية اللحظية لتدفقك النقدي.',
                [
                    'تتبع دقيق للمصروفات والإيرادات.',
                    'تقارير ضريبية ومالية تلقائية.',
                    'فهم أفضل لهوامش الربح لكل منتج/خدمة.'
                ]
            );
        }

        // حساب درجة الصحة المالية الحقيقية
        $baseScore = 50; // نقطة البداية

        // 1. عامل الإيرادات (كلما زاد الدخل زاد الاستقرار)
        if ($revenue > 100000) $baseScore += 20;
        elseif ($revenue > 50000) $baseScore += 10;

        // 2. عامل كفاءة الصرف (نسبة الميزانية)
        if ($revenue > 0) {
            $ratio = $marketingBudget / $revenue;
            if ($ratio < 0.20) $baseScore += 20; // صرف متزن
            elseif ($ratio > 0.40) $baseScore -= 10; // صرف عالي المخاطر
        }

        // 3. عامل الأدوات (استخدام نظام محاسبي)
        if (isset($answers['use_accounting_software']) && $answers['use_accounting_software'] === 'yes') {
            $baseScore += 10;
        }

        $scores['financial_health'] = min(100, max(0, $baseScore));

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'alerts' => $alerts,
            'summary' => 'الوضع المالي يتطلب مراقبة دقيقة لهوامش الربح.'
        ];
    }
}
