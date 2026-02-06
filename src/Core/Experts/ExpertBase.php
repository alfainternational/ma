<?php

namespace App\Core\Experts;

/**
 * Class ExpertBase
 * القاعدة الأساسية لجميع الخبراء الـ 10 في النظام.
 * توفر هيكلاً موحداً للتحليل، الاستنتاج، وتوليد الرؤى.
 */
abstract class ExpertBase {
    protected string $id;
    protected string $name;
    protected string $role;
    protected array $expertiseAreas;
    protected array $analysisFramework;

    public function __construct(array $config) {
        $this->id = $config['id'];
        $this->name = $config['name'];
        $this->role = $config['role'];
        $this->expertiseAreas = $config['expertise_areas'] ?? [];
        $this->analysisFramework = $config['analysis_framework'] ?? [];
    }

    /**
     * وظيفة التحليل الأساسية التي سيقوم كل خبير بتنفيذها بناءً على تخصصه.
     * @param array $answers إجابات العميل في الجلسة الحالية
     * @param array $context سياق الجلسة (القطاع، التوجه، إلخ)
     * @return array نتائج التحليل (نقاط، رؤى، تنبيهات)
     */
    abstract public function analyze(array $answers, array $context): array;

    /**
     * توليد رؤى ذكية في حال عدم وجود خدمة أو ميزة لدى العميل.
     * تطبيقة لمتطلب "تحليل الفوائد المفقودة".
     * 
     * @param string $serviceName اسم الخدمة المفقودة
     * @param string $reason أهمية وجود هذه الخدمة
     * @param array $benefits المميزات التي سيحصل عليها العميل
     * @return array تفاصيل الرؤية الخاصة بالفوائد المفقودة
     */
    protected function generateMissingServiceInsight(string $serviceName, string $reason, array $benefits): array {
        return [
            'type' => 'missing_service_opportunity',
            'service' => $serviceName,
            'importance' => $reason,
            'expected_benefits' => $benefits,
            'impact_of_absence' => "قد يؤدي عدم وجود {$serviceName} إلى فقدان فرص نمو حقيقية وضعف التنافسية في السوق.",
            'severity' => 'medium'
        ];
    }

    /**
     * الحصول على معلومات الخبير
     */
    public function getInfo(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'expertise' => $this->expertiseAreas
        ];
    }
}
