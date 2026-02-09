<?php
/**
 * Relationship Engine - محرك العلاقات والارتباطات
 * Links questions/answers, detects contradictions and opportunities
 */
class RelationshipEngine {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function analyzeRelationships(array $answers, array $context): array {
        $answersMap = $this->mapAnswers($answers);

        return [
            'consistency_violations' => $this->checkConsistencyRules($answersMap),
            'contradictions' => $this->detectContradictions($answersMap),
            'opportunities' => $this->detectOpportunities($answersMap, $context),
            'insights' => $this->generateInsights($answersMap, $context),
        ];
    }

    public function checkConsistencyRules(array $answers): array {
        $violations = [];

        // RULE_001: budget/revenue > 0.30 => critical
        $revenue = (float)($answers['annual_revenue'] ?? 0);
        $budget = (float)($answers['marketing_budget'] ?? 0) * 12;
        if ($revenue > 0 && $budget > 0 && ($budget / $revenue) > 0.30) {
            $violations[] = [
                'rule_id' => 'RULE_001',
                'name' => 'budget_revenue_consistency',
                'severity' => 'critical',
                'message' => 'الميزانية التسويقية تتجاوز 30% من الإيرادات - غير مستدام',
                'recommendation' => 'إعادة تقييم الميزانية أو زيادة الإيرادات',
                'expert' => 'financial_analyst',
            ];
        }

        // RULE_002: cac > ltv
        $cac = (float)($answers['customer_acquisition_cost'] ?? 0);
        $ltv = (float)($answers['customer_lifetime_value'] ?? 0);
        if ($cac > 0 && $ltv > 0 && $cac > $ltv) {
            $violations[] = [
                'rule_id' => 'RULE_002',
                'name' => 'cac_ltv_ratio',
                'severity' => 'critical',
                'message' => 'تكلفة اكتساب العميل أعلى من قيمة العميل',
                'recommendation' => 'تحسين كفاءة التسويق أو زيادة قيمة العميل',
                'expert' => 'financial_analyst',
            ];
        }

        // RULE_003: revenue/team < benchmark * 0.5
        $teamSize = (int)($answers['employee_count'] ?? 1);
        if ($teamSize > 0 && $revenue > 0 && ($revenue / $teamSize) < 100000) {
            $violations[] = [
                'rule_id' => 'RULE_003',
                'name' => 'team_revenue_efficiency',
                'severity' => 'high',
                'message' => 'إنتاجية الفريق منخفضة مقارنة بالقطاع',
                'recommendation' => 'تحسين العمليات أو إعادة هيكلة الفريق',
                'expert' => 'operations_expert',
            ];
        }

        // RULE_004: no digital in digital-required sectors
        $digitalScore = (int)($answers['digital_maturity_score'] ?? 50);
        $sector = $answers['sector'] ?? '';
        if ($digitalScore < 20 && in_array($sector, ['retail', 'fnb', 'fitness'])) {
            $violations[] = [
                'rule_id' => 'RULE_004',
                'name' => 'no_digital_presence_modern_sector',
                'severity' => 'high',
                'message' => 'غياب شبه كامل للتواجد الرقمي في قطاع يتطلب ذلك',
                'recommendation' => 'بناء الحضور الرقمي بشكل عاجل',
                'expert' => 'digital_marketing_expert',
            ];
        }

        return $violations;
    }

    public function detectContradictions(array $answers): array {
        $contradictions = [];

        // CONTRA_001: claims growth but declining metrics
        $selfAssessment = $answers['business_growth_assessment'] ?? '';
        $revTrend = $answers['revenue_trend'] ?? '';
        if (in_array($selfAssessment, ['growing', 'fast_growing']) && $revTrend === 'declining') {
            $contradictions[] = [
                'id' => 'CONTRA_001',
                'name' => 'claims_growth_but_declining',
                'message' => 'هناك تناقض بين تصريحك بأن العمل ينمو والأرقام التي تشير لانخفاض',
                'action' => 'request_clarification',
                'severity' => 'high',
            ];
        }

        // CONTRA_002: no competition but saturated market
        $competition = $answers['competition_level'] ?? '';
        $competitorCount = (int)($answers['competitor_count'] ?? 0);
        if ($competition === 'low' && $competitorCount > 10) {
            $contradictions[] = [
                'id' => 'CONTRA_002',
                'name' => 'no_competition_but_many_competitors',
                'message' => 'تقول أن المنافسة منخفضة لكن عدد المنافسين كبير',
                'action' => 'educate_and_clarify',
                'severity' => 'medium',
            ];
        }

        // High satisfaction but high churn
        $satisfaction = (float)($answers['customer_satisfaction'] ?? 0);
        $churnRate = (float)($answers['churn_rate'] ?? 0);
        if ($satisfaction > 8 && $churnRate > 20) {
            $contradictions[] = [
                'id' => 'CONTRA_003',
                'name' => 'high_satisfaction_high_churn',
                'message' => 'تناقض: رضا عملاء عالي لكن معدل انقطاع مرتفع',
                'action' => 'deep_dive_investigation',
                'severity' => 'high',
            ];
        }

        return $contradictions;
    }

    public function detectOpportunities(array $answers, array $context): array {
        $opportunities = [];

        // OPP_001: underutilized budget
        $budgetAllocated = (float)($answers['marketing_budget'] ?? 0);
        $budgetSpent = (float)($answers['marketing_budget_spent'] ?? $budgetAllocated);
        $revTrend = $answers['revenue_trend'] ?? '';
        if ($budgetAllocated > 0 && $budgetSpent < ($budgetAllocated * 0.5) && $revTrend === 'growing') {
            $opportunities[] = [
                'id' => 'OPP_001',
                'name' => 'underutilized_budget',
                'message' => 'هناك ميزانية غير مستغلة يمكن استثمارها للنمو',
                'recommendation' => 'زيادة الاستثمار التسويقي لتسريع النمو',
                'impact' => 'high',
            ];
        }

        // OPP_002: high satisfaction, low awareness
        $satisfaction = (float)($answers['customer_satisfaction'] ?? 0);
        $awareness = (float)($answers['brand_awareness'] ?? 5);
        if ($satisfaction > 8 && $awareness < 4) {
            $opportunities[] = [
                'id' => 'OPP_002',
                'name' => 'high_satisfaction_low_awareness',
                'message' => 'عملاء راضون جداً لكن الوعي بالعلامة منخفض',
                'recommendation' => 'برنامج إحالة مكثف واستثمار في بناء الوعي',
                'impact' => 'high',
            ];
        }

        // OPP_003: no digital marketing in growing market
        $hasWebsite = in_array($answers['has_website'] ?? '', ['yes', 'نعم', '1', true], true);
        $marketGrowing = ($answers['market_trend'] ?? '') === 'growing';
        if (!$hasWebsite && $marketGrowing) {
            $opportunities[] = [
                'id' => 'OPP_003',
                'name' => 'digital_opportunity',
                'message' => 'السوق ينمو وليس لديك تواجد رقمي - فرصة كبيرة',
                'recommendation' => 'بناء موقع إلكتروني وحضور رقمي فوراً',
                'impact' => 'high',
            ];
        }

        return $opportunities;
    }

    private function generateInsights(array $answers, array $context): array {
        $insights = [];
        $revenue = (float)($answers['annual_revenue'] ?? 0);
        $budget = (float)($answers['marketing_budget'] ?? 0);

        if ($revenue > 0 && $budget > 0) {
            $ratio = round(($budget * 12 / $revenue) * 100, 1);
            $insights[] = [
                'title' => 'نسبة الميزانية التسويقية',
                'description' => "ميزانيتك التسويقية تمثل {$ratio}% من إيراداتك السنوية",
                'impact' => $ratio > 15 ? 'warning' : ($ratio < 3 ? 'low_investment' : 'healthy'),
            ];
        }

        return $insights;
    }

    private function mapAnswers(array $answers): array {
        $map = [];
        foreach ($answers as $a) {
            $key = $a['question_id'] ?? $a['field_mapping'] ?? '';
            $map[$key] = $a['answer_value'] ?? null;
            if (isset($a['field_mapping'])) {
                $map[$a['field_mapping']] = $a['answer_value'] ?? null;
            }
        }
        return $map;
    }
}
