<?php
/**
 * FinancialAnalyst - Marketing Financial Analysis Expert
 *
 * Evaluates financial health of marketing operations including ROI,
 * budget sustainability, CAC/LTV ratios, and revenue projections.
 * Provides sector-specific benchmarks and red-flag detection.
 */
class FinancialAnalyst extends ExpertBase {

    private const SECTOR_BENCHMARKS = [
        'ecommerce'    => ['marketing_budget_percent' => 12, 'acceptable_cac' => 50,  'expected_roi' => 4.0],
        'saas'         => ['marketing_budget_percent' => 15, 'acceptable_cac' => 200, 'expected_roi' => 5.0],
        'retail'       => ['marketing_budget_percent' => 10, 'acceptable_cac' => 30,  'expected_roi' => 3.5],
        'services'     => ['marketing_budget_percent' => 8,  'acceptable_cac' => 100, 'expected_roi' => 3.0],
        'healthcare'   => ['marketing_budget_percent' => 7,  'acceptable_cac' => 150, 'expected_roi' => 3.5],
        'education'    => ['marketing_budget_percent' => 10, 'acceptable_cac' => 80,  'expected_roi' => 3.0],
        'real_estate'  => ['marketing_budget_percent' => 5,  'acceptable_cac' => 500, 'expected_roi' => 6.0],
        'food'         => ['marketing_budget_percent' => 8,  'acceptable_cac' => 20,  'expected_roi' => 3.0],
        'technology'   => ['marketing_budget_percent' => 14, 'acceptable_cac' => 250, 'expected_roi' => 5.0],
        'general'      => ['marketing_budget_percent' => 10, 'acceptable_cac' => 100, 'expected_roi' => 3.5],
    ];

    private const RED_FLAG_THRESHOLDS = [
        'budget_to_revenue_max'    => 0.30,
        'cac_exceeds_ltv'          => true,
        'negative_cash_flow_months'=> 3,
        'revenue_decline_percent'  => -10,
    ];

    protected function initialize(): void {
        $this->id = 'financial_analyst';
        $this->name = 'خبير التحليل المالي';
        $this->role = 'تحليل الجدوى المالية للأنشطة التسويقية وتقييم العوائد وإدارة الميزانيات';
        $this->expertiseAreas = [
            'financial_analysis',
            'roi_calculation',
            'budget_optimization',
            'cost_management',
            'revenue_forecasting',
        ];
        $this->personality = [
            'analytical'   => 0.95,
            'conservative' => 0.8,
            'detail_oriented' => 0.9,
            'data_driven'  => 0.95,
            'risk_aware'   => 0.85,
        ];
        $this->decisionWeight = 0.85;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $sector = $context['sector'] ?? 'general';
        $benchmarks = self::SECTOR_BENCHMARKS[$sector] ?? self::SECTOR_BENCHMARKS['general'];

        $financials = $this->extractFinancials($answers, $context);
        $healthAssessment = $this->assessFinancialHealth($financials, $benchmarks);
        $redFlags = $this->detectRedFlags($financials, $benchmarks);
        $roiProjections = $this->calculateROIProjections($financials, $benchmarks);
        $budgetAnalysis = $this->analyzeBudget($financials, $benchmarks, $sector);

        $sections = [
            'financial_health_assessment' => $healthAssessment,
            'budget_recommendations'      => $budgetAnalysis,
            'roi_projections'             => $roiProjections,
            'red_flags'                   => $redFlags,
        ];

        $overallScore = $healthAssessment['overall_score'] ?? 50;
        $confidence = $this->calculateConfidence($financials);

        $result = $this->buildResult(
            $sections,
            [
                'financial_health' => $overallScore,
                'roi_score'        => $roiProjections['roi_score'] ?? 50,
                'budget_score'     => $budgetAnalysis['budget_score'] ?? 50,
                'red_flag_count'   => count($redFlags),
            ],
            [],
            [],
            $confidence
        );

        $result['insights'] = $this->generateInsights($result);
        $result['recommendations'] = $this->generateRecommendations($result);

        return $result;
    }

    public function generateInsights(array $analysisResult): array {
        $insights = [];
        $sections = $analysisResult['sections'] ?? [];
        $scores = $analysisResult['scores'] ?? [];

        $healthScore = $scores['financial_health'] ?? 50;
        $insights[] = $this->formatInsight(
            'الصحة المالية التسويقية',
            sprintf('مستوى الصحة المالية للأنشطة التسويقية: %s (%.0f/100)', $this->getScoreLabel($healthScore), $healthScore),
            $healthScore >= 60 ? 'positive' : ($healthScore >= 40 ? 'neutral' : 'negative'),
            0.9
        );

        // Red flag insights
        $redFlags = $sections['red_flags'] ?? [];
        foreach ($redFlags as $flag) {
            $insights[] = $this->formatInsight(
                $flag['title'],
                $flag['description'],
                'warning',
                $flag['confidence'] ?? 0.85
            );
        }

        // ROI insight
        $roiScore = $scores['roi_score'] ?? 50;
        if ($roiScore < 40) {
            $insights[] = $this->formatInsight(
                'العائد على الاستثمار التسويقي منخفض',
                'عوائد الأنشطة التسويقية الحالية أقل من المعدل المقبول للقطاع، يجب مراجعة تخصيص الميزانيات',
                'negative',
                0.85
            );
        } elseif ($roiScore >= 75) {
            $insights[] = $this->formatInsight(
                'عائد تسويقي ممتاز',
                'الأنشطة التسويقية تحقق عوائد أعلى من متوسط القطاع، يُنصح بزيادة الاستثمار',
                'positive',
                0.85
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $scores = $analysisResult['scores'] ?? [];
        $sections = $analysisResult['sections'] ?? [];
        $redFlagCount = $scores['red_flag_count'] ?? 0;

        if ($redFlagCount > 0) {
            $recommendations[] = $this->formatRecommendation(
                'معالجة المؤشرات المالية الحرجة',
                sprintf('تم اكتشاف %d مؤشرات تحتاج تدخلاً فورياً', $redFlagCount),
                'critical',
                [
                    'مراجعة فورية لبنود الإنفاق التسويقي',
                    'إيقاف الحملات ذات العائد السلبي',
                    'إعادة توزيع الميزانية نحو القنوات الأعلى كفاءة',
                ]
            );
        }

        $budgetScore = $scores['budget_score'] ?? 50;
        if ($budgetScore < 50) {
            $recommendations[] = $this->formatRecommendation(
                'إعادة هيكلة الميزانية التسويقية',
                'توزيع الميزانية الحالي لا يتوافق مع معايير القطاع ويحتاج تعديلاً',
                'high',
                [
                    'مقارنة التوزيع الحالي بمعايير القطاع',
                    'تخصيص 60% للقنوات المثبتة و30% للنمو و10% للتجربة',
                    'وضع حد أقصى لتكلفة اكتساب العميل',
                    'مراجعة شهرية لأداء كل قناة',
                ]
            );
        }

        $roiScore = $scores['roi_score'] ?? 50;
        if ($roiScore >= 70) {
            $recommendations[] = $this->formatRecommendation(
                'زيادة الاستثمار التسويقي',
                'العوائد الحالية تبرر زيادة الميزانية التسويقية لتسريع النمو',
                'medium',
                [
                    'زيادة الميزانية بنسبة 15-25% في القنوات الأعلى أداءً',
                    'اختبار قنوات جديدة بميزانية تجريبية',
                    'الاستثمار في أتمتة التسويق لتحسين الكفاءة',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function extractFinancials(array $answers, array $context): array {
        return [
            'revenue'            => (float) $this->extractValue($answers, 'revenue', 0),
            'profit_margin'      => (float) $this->extractValue($answers, 'profit_margin', 0),
            'marketing_budget'   => (float) $this->extractValue($answers, 'marketing_budget', 0),
            'cac'                => (float) $this->extractValue($answers, 'cac', 0),
            'ltv'                => (float) $this->extractValue($answers, 'ltv', 0),
            'roi'                => (float) $this->extractValue($answers, 'roi', 0),
            'romi'               => (float) $this->extractValue($answers, 'romi', 0),
            'cash_flow'          => $this->extractValue($answers, 'cash_flow', 'positive'),
            'revenue_trend'      => $this->extractValue($answers, 'revenue_trend', 'stable'),
            'monthly_leads'      => (int) $this->extractValue($answers, 'monthly_leads', 0),
            'conversion_rate'    => (float) $this->extractValue($answers, 'conversion_rate', 0),
            'sector'             => $context['sector'] ?? 'general',
        ];
    }

    private function assessFinancialHealth(array $financials, array $benchmarks): array {
        $scores = [];

        // Budget-to-revenue ratio score
        if ($financials['revenue'] > 0) {
            $budgetRatio = ($financials['marketing_budget'] / $financials['revenue']) * 100;
            $idealRatio = $benchmarks['marketing_budget_percent'];
            $deviation = abs($budgetRatio - $idealRatio);
            $scores['budget_ratio'] = max(0, 100 - ($deviation * 5));
        } else {
            $scores['budget_ratio'] = 20;
        }

        // CAC/LTV ratio score
        if ($financials['ltv'] > 0 && $financials['cac'] > 0) {
            $ratio = $financials['ltv'] / $financials['cac'];
            $scores['cac_ltv'] = min(100, $ratio * 25);
        } else {
            $scores['cac_ltv'] = 40;
        }

        // ROI score
        $expectedRoi = $benchmarks['expected_roi'];
        if ($financials['roi'] > 0) {
            $scores['roi'] = min(100, ($financials['roi'] / $expectedRoi) * 70);
        } else {
            $scores['roi'] = 30;
        }

        // Profit margin score
        $scores['profit_margin'] = min(100, max(0, $financials['profit_margin'] * 2.5));

        // Cash flow score
        $scores['cash_flow'] = match ($financials['cash_flow']) {
            'positive', 'growing' => 80,
            'stable'              => 60,
            'declining'           => 30,
            'negative'            => 10,
            default               => 50,
        };

        // Revenue trend score
        $scores['revenue_trend'] = match ($financials['revenue_trend']) {
            'growing_fast' => 95,
            'growing'      => 80,
            'stable'       => 55,
            'declining'    => 25,
            'declining_fast'=> 10,
            default        => 50,
        };

        $weights = [
            'budget_ratio' => 0.15, 'cac_ltv' => 0.20, 'roi' => 0.25,
            'profit_margin' => 0.15, 'cash_flow' => 0.15, 'revenue_trend' => 0.10,
        ];

        $overall = 0;
        foreach ($weights as $metric => $weight) {
            $overall += ($scores[$metric] ?? 50) * $weight;
        }

        return [
            'overall_score'   => round($overall, 1),
            'label'           => $this->getScoreLabel($overall),
            'component_scores'=> $scores,
        ];
    }

    private function detectRedFlags(array $financials, array $benchmarks): array {
        $flags = [];

        // Budget exceeds 30% of revenue
        if ($financials['revenue'] > 0) {
            $budgetRatio = $financials['marketing_budget'] / $financials['revenue'];
            if ($budgetRatio > self::RED_FLAG_THRESHOLDS['budget_to_revenue_max']) {
                $flags[] = [
                    'title'       => 'إنفاق تسويقي مفرط',
                    'description' => sprintf('الميزانية التسويقية تمثل %.0f%% من الإيرادات وهي أعلى من الحد الأقصى المقبول (30%%)', $budgetRatio * 100),
                    'severity'    => 'critical',
                    'confidence'  => 0.95,
                ];
            }
        }

        // CAC exceeds LTV
        if ($financials['cac'] > 0 && $financials['ltv'] > 0 && $financials['cac'] > $financials['ltv']) {
            $flags[] = [
                'title'       => 'تكلفة اكتساب العميل تتجاوز قيمته',
                'description' => sprintf('تكلفة اكتساب العميل (%.0f) تتجاوز القيمة الدائمة للعميل (%.0f)، مما يعني خسارة في كل عميل جديد', $financials['cac'], $financials['ltv']),
                'severity'    => 'critical',
                'confidence'  => 0.95,
            ];
        }

        // Negative cash flow
        if ($financials['cash_flow'] === 'negative') {
            $flags[] = [
                'title'       => 'تدفق نقدي سلبي',
                'description' => 'التدفق النقدي سلبي مما يهدد استدامة الأنشطة التسويقية والعمليات العامة',
                'severity'    => 'high',
                'confidence'  => 0.9,
            ];
        }

        // Declining revenue
        if (in_array($financials['revenue_trend'], ['declining', 'declining_fast'])) {
            $flags[] = [
                'title'       => 'انخفاض الإيرادات',
                'description' => 'الإيرادات في اتجاه هبوطي مما يستوجب مراجعة فورية للاستراتيجية التسويقية',
                'severity'    => 'high',
                'confidence'  => 0.85,
            ];
        }

        return $flags;
    }

    private function calculateROIProjections(array $financials, array $benchmarks): array {
        $currentROI = $financials['roi'] > 0 ? $financials['roi'] : 1.0;
        $expectedROI = $benchmarks['expected_roi'];

        $optimisticMultiplier = 1.3;
        $conservativeMultiplier = 0.85;

        $projections = [
            'current_roi'     => $currentROI,
            'expected_roi'    => $expectedROI,
            'gap'             => round($expectedROI - $currentROI, 2),
            'optimistic_roi'  => round($currentROI * $optimisticMultiplier, 2),
            'conservative_roi'=> round($currentROI * $conservativeMultiplier, 2),
            'roi_score'       => min(100, max(0, ($currentROI / max(0.1, $expectedROI)) * 70)),
        ];

        $projections['roi_score'] = round($projections['roi_score'], 1);

        return $projections;
    }

    private function analyzeBudget(array $financials, array $benchmarks, string $sector): array {
        $budget = $financials['marketing_budget'];
        $revenue = $financials['revenue'];
        $idealPercent = $benchmarks['marketing_budget_percent'];

        $currentPercent = $revenue > 0 ? ($budget / $revenue) * 100 : 0;
        $idealBudget = $revenue * ($idealPercent / 100);
        $difference = $budget - $idealBudget;

        $budgetScore = 50.0;
        if ($revenue > 0) {
            $deviation = abs($currentPercent - $idealPercent);
            $budgetScore = max(0, 100 - ($deviation * 6));
        }

        return [
            'current_budget'       => $budget,
            'current_percent'      => round($currentPercent, 1),
            'ideal_percent'        => $idealPercent,
            'ideal_budget'         => round($idealBudget, 0),
            'difference'           => round($difference, 0),
            'budget_score'         => round($budgetScore, 1),
            'recommendation'       => $difference > 0
                ? 'الميزانية أعلى من المعيار - يجب تحسين الكفاءة أو خفض الإنفاق'
                : ($difference < 0 ? 'الميزانية أقل من المعيار - فرصة لزيادة الاستثمار' : 'الميزانية متوافقة مع معايير القطاع'),
            'sector'               => $sector,
            'sector_benchmark'     => $idealPercent . '%',
        ];
    }
}
