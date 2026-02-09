<?php
/**
 * Inference Engine - محرك الاستنتاج والتنبؤ
 * Pattern detection and prediction
 */
class InferenceEngine {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function runInference(array $answers, array $context, array $scores): array {
        $answersMap = $this->mapAnswers($answers);
        $patterns = $this->detectPatterns($answersMap, $context);
        $planType = $this->recommendPlanType($context, $scores);
        $predictions = $this->predictOutcomes($context, $scores);
        $scenarios = $this->runScenarioAnalysis($context, $scores);

        return [
            'patterns' => $patterns,
            'recommended_plan' => $planType,
            'predictions' => $predictions,
            'scenarios' => $scenarios,
            'insights' => $this->extractInsights($patterns, $planType),
        ];
    }

    public function detectPatterns(array $answers, array $context): array {
        $patterns = [];

        // INF_001: struggling business
        $revTrend = $answers['revenue_trend'] ?? $context['revenue_trend'] ?? '';
        $digitalScore = (int)($context['digital_maturity'] ?? $answers['digital_maturity_score'] ?? 50);
        $competition = $answers['competition_level'] ?? $context['competition_level'] ?? '';

        if ($revTrend === 'declining' && $digitalScore < 30 && in_array($competition, ['high', 'very_high'])) {
            $patterns[] = [
                'id' => 'INF_001',
                'name' => 'أعمال في مرحلة حرجة',
                'confidence' => 0.90,
                'recommended_plan' => 'emergency',
                'recommended_actions' => ['تكتيكات عاجلة لزيادة الإيرادات', 'تحسين التكاليف', 'بناء الحضور الرقمي', 'إعادة التموضع التنافسي'],
                'assigned_experts' => ['chief_strategist', 'financial_analyst', 'risk_manager'],
                'severity' => 'critical',
            ];
        }

        // INF_002: growth opportunity
        $marketingSpend = (float)($answers['marketing_budget_percent'] ?? 5);
        $satisfaction = (float)($answers['customer_satisfaction'] ?? 5);
        if ($revTrend === 'growing' && $marketingSpend < 5 && $satisfaction > 7) {
            $patterns[] = [
                'id' => 'INF_002',
                'name' => 'فرصة نمو كبيرة غير مستغلة',
                'confidence' => 0.85,
                'recommended_plan' => 'growth',
                'recommended_actions' => ['زيادة الميزانية التسويقية', 'توسيع القنوات', 'برنامج إحالة', 'حملات نمو مدفوعة'],
                'assigned_experts' => ['chief_strategist', 'digital_marketing_expert'],
                'severity' => 'opportunity',
            ];
        }

        // INF_003: digital transformation ready
        $revenue = (float)($answers['annual_revenue'] ?? 0);
        $teamSize = (int)($answers['employee_count'] ?? 0);
        $leadershipSupport = (float)($answers['leadership_support'] ?? 5);
        if ($revenue > 1000000 && $digitalScore < 40 && $teamSize > 5 && $leadershipSupport > 7) {
            $patterns[] = [
                'id' => 'INF_003',
                'name' => 'جاهز للتحول الرقمي',
                'confidence' => 0.80,
                'recommended_plan' => 'transformation',
                'recommended_actions' => ['خطة تحول رقمي شاملة', 'تدريب الفريق', 'بناء البنية التحتية الرقمية'],
                'assigned_experts' => ['digital_marketing_expert', 'operations_expert', 'data_scientist'],
                'severity' => 'opportunity',
            ];
        }

        // INF_004: brand building priority
        $brandAwareness = (float)($answers['brand_awareness'] ?? 5);
        $productQuality = (float)($answers['product_quality'] ?? 5);
        $yearsInBusiness = (float)($answers['years_in_business'] ?? 5);
        if ($brandAwareness < 3 && $productQuality > 7 && $yearsInBusiness < 3) {
            $patterns[] = [
                'id' => 'INF_004',
                'name' => 'بناء العلامة التجارية أولوية',
                'confidence' => 0.85,
                'recommended_plan' => 'treatment',
                'recommended_actions' => ['بناء هوية بصرية', 'تسويق بالمحتوى', 'علاقات عامة', 'شراكات مع مؤثرين'],
                'assigned_experts' => ['brand_strategist', 'digital_marketing_expert'],
                'severity' => 'high',
            ];
        }

        return $patterns;
    }

    public function predictOutcomes(array $context, array $scores): array {
        $overallScore = $scores['overall'] ?? 50;
        $riskScore = $scores['risk_score'] ?? 50;
        $opportunityScore = $scores['opportunity_score'] ?? 50;

        $baseGrowth = ($overallScore - 50) / 100; // -0.5 to 0.5
        $riskFactor = 1 - ($riskScore / 200); // 0.5 to 1.0
        $oppFactor = 1 + ($opportunityScore / 200); // 1.0 to 1.5

        return [
            'revenue_forecast' => [
                '6_months' => round($baseGrowth * $riskFactor * 100, 1) . '% تغيير متوقع',
                '12_months' => round($baseGrowth * $oppFactor * 200, 1) . '% تغيير متوقع',
                'confidence' => round(0.6 + ($overallScore / 500), 2),
            ],
            'risk_without_action' => [
                '6_months' => $riskScore > 60 ? 'خطر جدي على الأداء' : 'مخاطر محدودة',
                '12_months' => $riskScore > 60 ? 'خطر على استمرارية العمل' : 'وضع مستقر',
            ],
            'improvement_potential' => [
                'with_recommended_plan' => min(100, $overallScore + 25) . ' درجة متوقعة',
                'timeline' => $overallScore < 30 ? '6-12 شهر' : '3-6 أشهر',
            ],
        ];
    }

    public function runScenarioAnalysis(array $context, array $scores): array {
        $current = $scores['overall'] ?? 50;

        return [
            'conservative' => [
                'label' => 'سيناريو متحفظ',
                'growth_rate' => 5,
                'projections' => [$current, $current + 3, $current + 5, $current + 7, $current + 10],
                'description' => 'تطبيق الحد الأدنى من التوصيات',
            ],
            'moderate' => [
                'label' => 'سيناريو متوسط',
                'growth_rate' => 15,
                'projections' => [$current, $current + 5, $current + 12, $current + 18, $current + 25],
                'description' => 'تطبيق معظم التوصيات بشكل منتظم',
            ],
            'aggressive' => [
                'label' => 'سيناريو متفائل',
                'growth_rate' => 30,
                'projections' => [$current, $current + 8, $current + 20, $current + 30, $current + 40],
                'description' => 'تطبيق كامل التوصيات مع استثمار إضافي',
            ],
        ];
    }

    public function recommendPlanType(array $context, array $scores): string {
        $overall = $scores['overall'] ?? 50;
        $risk = $scores['risk_score'] ?? 50;

        if ($risk > 70 || $overall < 20) return 'emergency';
        if ($overall < 40) return 'treatment';
        if ($overall < 70) return 'growth';
        return 'transformation';
    }

    private function extractInsights(array $patterns, string $planType): array {
        $insights = [];
        foreach ($patterns as $p) {
            $insights[] = [
                'title' => $p['name'],
                'description' => 'ثقة: ' . ($p['confidence'] * 100) . '%',
                'source' => 'inference_engine',
                'confidence' => $p['confidence'],
            ];
        }
        $insights[] = [
            'title' => 'نوع الخطة الموصى بها',
            'description' => PLAN_TYPES[$planType]['ar'] ?? $planType,
            'source' => 'inference_engine',
        ];
        return $insights;
    }

    private function mapAnswers(array $answers): array {
        $map = [];
        foreach ($answers as $a) {
            $key = $a['question_id'] ?? '';
            $map[$key] = $a['answer_value'] ?? null;
            if (isset($a['field_mapping'])) {
                $map[$a['field_mapping']] = $a['answer_value'] ?? null;
            }
        }
        return $map;
    }
}
