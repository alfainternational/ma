<?php

namespace App\Core\Experts;

/**
 * Class DataScientist
 * خبير علوم البيانات (Data Scientist).
 * مسؤول عن تحليل الأرقام، الترابطات، التوقعات، واكتشاف الأنماط الغريبة.
 */
class DataScientist extends ExpertBase {
    
    public function analyze(array $answers, array $context): array {
        $insights = [];
        $scores = ['data_maturity' => 0];

        $usesCRM = ($answers['uses_crm_system'] ?? 'no') === 'yes';

        // تحليل نضج البيانات
        if (!$usesCRM) {
            $insights[] = $this->generateMissingServiceInsight(
                'نظام إدارة علاقات العملاء (CRM)',
                'الاعتماد على الأسماء والذاكرة أو ملفات Excel البسيطة يجعل من المستحيل تحليل أنماط الشراء وتوقع القيمة طويلة الأمد للعميل.',
                [
                    'جمع بيانات العملاء في مكان واحد آمن ومنظم.',
                    'القدرة على تقسيم العملاء (Segmentation) لاستهدافهم بدقة.',
                    'توقع المبيعات المستقبلية بناءً على البيانات التاريخية.'
                ]
            );
        }

        $scores['data_maturity'] = $usesCRM ? 85 : 20;

        return [
            'expert_id' => $this->id,
            'scores' => $scores,
            'insights' => $insights,
            'summary' => 'البيانات هي النفط الجديد، وتحتاج لنظام مركزي لجمعها وتحليلها.'
        ];
    }
}
