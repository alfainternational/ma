<?php

namespace App\Core\Experts;

/**
 * Class OperationsExpert
 * خبير العمليات (Operations Expert).
 * مسؤول عن الفعالية التشغيلية، الموارد، التنفيذ، والربط بين الاستراتيجية والتطبيق.
 */
class OperationsExpert extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $scores = ['operational_efficiency' => 0];

        $hasProcesses = ($answers['has_documented_processes'] ?? 'no') === 'yes';

        // تحليل الفعالية التشغيلية
        if (!$hasProcesses) {
            $insights[] = $this->generateMissingServiceInsight(
                'توثيق إجراءات التشغيل المعيارية (SOPs)',
                'دون إجراءات موثقة، يعتمد العمل كلياً على أشخاص بدلاً من أنظمة، مما يجعل التوسع (Scalability) مخاطرة كبيرة.',
                [
                    'ضمان جودة ثابتة للخدمة/المنتج بغض النظر عن الشخص المنفذ.',
                    'تسهيل وتدريب الموظفين الجدد بسرعة وكفاءة.',
                    'تقليل الهدر الزمني والمادي الناتج عن العشوائية.'
                ]
            );
        }

        $scores['operations_efficiency'] = $hasProcesses ? 85 : 45;
        $scores['strategy_maturity'] = $hasProcesses ? 70 : 50;

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'summary' => 'بناء الأنظمة والإجراءات هو ما يحول المشروع الصغير إلى منشأة قابلة للتوسع.'
        ];
    }
}
