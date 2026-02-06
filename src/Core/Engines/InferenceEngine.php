<?php

namespace App\Core\Engines;

use App\Core\Experts\ChiefStrategist;
use App\Core\Experts\FinancialAnalyst;
use App\Core\Experts\MarketAnalyst;
use App\Core\Experts\DigitalMarketingExpert;
use App\Core\Experts\BrandStrategist;
use App\Core\Experts\ConsumerPsychologist;
use App\Core\Experts\DataScientist;
use App\Core\Experts\OperationsExpert;
use App\Core\Experts\RiskManager;
use App\Core\Experts\InnovationScout;

/**
 * Class InferenceEngine
 * المحرك الرئيسي للاستنتاج (Inference Engine).
 * يعمل كمنسق بين جميع الخبراء الـ 10 لتحليل البيانات وتوليد رؤية موحدة.
 */
class InferenceEngine {
    private array $experts = [];

    public function __construct() {
        $this->loadExperts();
    }

    /**
     * تحميل جميع الخبراء الافتراضيين (سيتم توسيع القائمة لاحقاً).
     */
    private function loadExperts(): void {
        // إضافة الخبراء المنفذين حالياً
        $this->experts[] = new ChiefStrategist([
            'id' => 'chief_strategist',
            'name' => 'كبير الاستراتيجيين',
            'role' => 'استراتيجية الأعمال والنمو',
            'expertise_areas' => ['Strategy', 'Scale-up'],
            'analysis_framework' => ['SWOT', 'PESTEL']
        ]);

        $this->experts[] = new FinancialAnalyst([
            'id' => 'financial_analyst',
            'name' => 'خبير التحليل المالي',
            'role' => 'التحليل المالي والربحية',
            'expertise_areas' => ['Financial KPIs', 'Budgeting'],
            'analysis_framework' => ['Financial Health Score']
        ]);

        $this->experts[] = new MarketAnalyst([
            'id' => 'market_analyst',
            'name' => 'خبير تحليل السوق',
            'role' => 'تحليل المنافسة والفرص',
            'expertise_areas' => ['Market Research', 'Competitive Intelligence'],
            'analysis_framework' => ['PESTEL', 'Competitor Matrix']
        ]);

        $this->experts[] = new DigitalMarketingExpert([
            'id' => 'digital_marketing_expert',
            'name' => 'خبير التسويق الرقمي',
            'role' => 'التواجد الرقمي والحملات',
            'expertise_areas' => ['SEO', 'SEM', 'Social Media'],
            'analysis_framework' => ['Digital Maturity Model']
        ]);

        $this->experts[] = new BrandStrategist([
            'id' => 'brand_strategist',
            'name' => 'خبير العلامة التجارية',
            'role' => 'الهوية والرسالة التسويقية',
            'expertise_areas' => ['Branding', 'Positioning'],
            'analysis_framework' => ['Brand Equity Model']
        ]);

        $this->experts[] = new ConsumerPsychologist([
            'id' => 'consumer_psychologist',
            'name' => 'خبير علم نفس المستهلك',
            'role' => 'فهم سلوك ودوافع العميل',
            'expertise_areas' => ['Customer Behavior', 'Psychographics'],
            'analysis_framework' => ['Customer Journey Mapping']
        ]);

        $this->experts[] = new DataScientist([
            'id' => 'data_scientist',
            'name' => 'خبير علوم البيانات',
            'role' => 'تحليل الأنماط والتوقعات',
            'expertise_areas' => ['Predictive Analytics', 'Customer Data'],
            'analysis_framework' => ['Pattern Recognition']
        ]);

        $this->experts[] = new OperationsExpert([
            'id' => 'operations_expert',
            'name' => 'خبير العمليات والفعالية',
            'role' => 'تحسين الأداء والتوسع',
            'expertise_areas' => ['Efficiency', 'Scalability'],
            'analysis_framework' => ['Standard Operating Procedures']
        ]);

        $this->experts[] = new RiskManager([
            'id' => 'risk_manager',
            'name' => 'مدير إدارة المخاطر',
            'role' => 'حماية العمل واستمراريته',
            'expertise_areas' => ['Business Continuity', 'Risk Mitigation'],
            'analysis_framework' => ['Risk Matrix']
        ]);

        $this->experts[] = new InnovationScout([
            'id' => 'innovation_scout',
            'name' => 'مستكشف الابتكار و AI',
            'role' => 'استشراف المستقبل والتقنيات',
            'expertise_areas' => ['Innovation', 'AI Integration'],
            'analysis_framework' => ['Innovation Readiness']
        ]);

        // ملاحظة: سيتم إضافة الخبراء الثمانية المتبقين هنا تباعاً (التسويق الرقمي، العلامة التجارية، إلخ)
    }

    /**
     * تشغيل عملية الاستنتاج الشاملة.
     * @param array $answers إجابات العميل من قاعدة البيانات
     * @param array $context سياق الجلسة (القطاع، الأهداف، إلخ)
     * @return array الخلاصة النهائية للمحرك
     */
    public function runInference(array $answers, array $context): array {
        $fullAnalysis = [
            'overview_scores' => [
                'maturity' => 0,
                'digital' => 0,
                'operations' => 0,
                'risk' => 0
            ],
            'expert_insights' => [],
            'critical_alerts' => [],
            'swot_synthesis' => [
                'strengths' => [],
                'weaknesses' => [],
                'opportunities' => [],
                'threats' => []
            ],
            'strategic_recommendations' => []
        ];

        foreach ($this->experts as $expert) {
            $result = $expert->analyze($answers, $context);

            // تجميع الدرجات وتوحيدها للفرونت إند
            if (isset($result['scores'])) {
                if (isset($result['scores']['strategy_maturity'])) $fullAnalysis['overview_scores']['maturity'] = $result['scores']['strategy_maturity'];
                if (isset($result['scores']['digital_maturity'])) $fullAnalysis['overview_scores']['digital'] = $result['scores']['digital_maturity'];
                if (isset($result['scores']['operations_efficiency'])) $fullAnalysis['overview_scores']['operations'] = $result['scores']['operations_efficiency'];
                if (isset($result['scores']['risk_score'])) $fullAnalysis['overview_scores']['risk'] = $result['scores']['risk_score'];
            }

            // تجميع الرؤى مع إضافة المعرف الفريد للخبير للتنسيق في الفرونت إند
            if (isset($result['insights'])) {
                foreach ($result['insights'] as $insight) {
                    $insight['expert_id'] = $expert->getInfo()['id'];
                    $insight['expert_name'] = $expert->getInfo()['name'];
                    $fullAnalysis['expert_insights'][] = $insight;
                }
            }

            // تجميع التنبيهات الحرجة
            if (isset($result['alerts'])) {
                $fullAnalysis['critical_alerts'] = array_merge($fullAnalysis['critical_alerts'], $result['alerts']);
            }

            // تجميع SWOT
            if (isset($result['swot'])) {
                foreach ($result['swot'] as $key => $items) {
                    $fullAnalysis['swot_synthesis'][$key] = array_merge($fullAnalysis['swot_synthesis'][$key], $items);
                }
            }
        }

        // تحويل overview_scores للتنسيق الذي يتوقعه results.js (مباشرة وليس array_merge غامض)
        $fullAnalysis['scores'] = $fullAnalysis['overview_scores'];

        return $fullAnalysis;
    }
}
