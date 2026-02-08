<?php
/**
 * RiskManager - Risk Assessment Expert
 *
 * Identifies, quantifies and prioritizes business and marketing risks.
 * Provides mitigation strategies and contingency planning.
 */
class RiskManager extends ExpertBase {

    private const RISK_CATEGORIES = [
        'financial'    => ['weight' => 0.30, 'label' => 'المخاطر المالية'],
        'market'       => ['weight' => 0.25, 'label' => 'مخاطر السوق'],
        'operational'  => ['weight' => 0.20, 'label' => 'المخاطر التشغيلية'],
        'competitive'  => ['weight' => 0.15, 'label' => 'المخاطر التنافسية'],
        'compliance'   => ['weight' => 0.10, 'label' => 'مخاطر الامتثال'],
    ];

    private const RISK_LEVELS = [
        'critical' => ['min' => 75, 'label' => 'حرج', 'color' => 'danger'],
        'high'     => ['min' => 50, 'label' => 'مرتفع', 'color' => 'warning'],
        'moderate' => ['min' => 25, 'label' => 'متوسط', 'color' => 'info'],
        'low'      => ['min' => 0,  'label' => 'منخفض', 'color' => 'success'],
    ];

    protected function initialize(): void {
        $this->id = 'risk_manager';
        $this->name = 'خبير إدارة المخاطر';
        $this->role = 'تحديد وتقييم المخاطر التسويقية والتجارية ووضع خطط التخفيف والطوارئ';
        $this->expertiseAreas = [
            'risk_assessment',
            'mitigation_planning',
            'compliance_review',
            'contingency_planning',
            'vulnerability_analysis',
        ];
        $this->personality = [
            'cautious'    => 0.9,
            'analytical'  => 0.85,
            'thorough'    => 0.9,
            'pragmatic'   => 0.8,
            'proactive'   => 0.75,
        ];
        $this->decisionWeight = 0.6;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $riskScores = $this->assessRiskCategories($answers, $context, $scores);
        $overallRisk = $this->calculateOverallRisk($riskScores);
        $vulnerabilities = $this->identifyVulnerabilities($answers, $context, $riskScores);
        $mitigationPlan = $this->buildMitigationPlan($riskScores, $vulnerabilities);

        $sections = [
            'risk_landscape'          => [
                'overall_risk'    => $overallRisk,
                'risk_level'      => $this->getRiskLevel($overallRisk),
                'category_scores' => $riskScores,
            ],
            'vulnerability_assessment' => $vulnerabilities,
            'mitigation_plan'          => $mitigationPlan,
        ];

        $allScores = [
            'overall_risk'      => $overallRisk,
            'risk_preparedness' => max(0, 100 - $overallRisk),
            'vulnerability_index' => $this->calculateVulnerabilityIndex($vulnerabilities),
        ];

        $confidence = $this->calculateConfidence($answers);

        $result = $this->buildResult($sections, $allScores, [], [], $confidence);
        $result['insights'] = $this->generateInsights($result);
        $result['recommendations'] = $this->generateRecommendations($result);

        return $result;
    }

    public function generateInsights(array $analysisResult): array {
        $insights = [];
        $overallRisk = $analysisResult['scores']['overall_risk'] ?? 50;
        $categories = $analysisResult['sections']['risk_landscape']['category_scores'] ?? [];

        $insights[] = $this->formatInsight(
            'مستوى المخاطر العام',
            sprintf('مستوى المخاطر الإجمالي: %s (%.0f/100). %s',
                $this->getRiskLevel($overallRisk)['label'],
                $overallRisk,
                $overallRisk >= 60 ? 'يتطلب اهتماماً فورياً' : 'ضمن النطاق المقبول'
            ),
            $overallRisk >= 60 ? 'negative' : ($overallRisk >= 40 ? 'warning' : 'positive'),
            0.85
        );

        // Highlight highest risk categories
        arsort($categories);
        foreach (array_slice($categories, 0, 2, true) as $cat => $score) {
            if ($score >= 50) {
                $label = self::RISK_CATEGORIES[$cat]['label'] ?? $cat;
                $insights[] = $this->formatInsight(
                    "خطر مرتفع: {$label}",
                    sprintf('%s تسجل %.0f/100 وتحتاج خطة تخفيف عاجلة', $label, $score),
                    'warning',
                    0.8
                );
            }
        }

        // Vulnerability count insight
        $vulnCount = count($analysisResult['sections']['vulnerability_assessment'] ?? []);
        if ($vulnCount > 0) {
            $insights[] = $this->formatInsight(
                'نقاط الضعف المكتشفة',
                sprintf('تم تحديد %d نقطة ضعف تحتاج معالجة', $vulnCount),
                $vulnCount >= 5 ? 'negative' : 'warning',
                0.8
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $overallRisk = $analysisResult['scores']['overall_risk'] ?? 50;
        $categories = $analysisResult['sections']['risk_landscape']['category_scores'] ?? [];

        if ($overallRisk >= 70) {
            $recommendations[] = $this->formatRecommendation(
                'خطة طوارئ لإدارة المخاطر',
                'مستوى المخاطر حرج ويتطلب تدخلاً فورياً مع خطة طوارئ شاملة',
                'critical',
                [
                    'إيقاف جميع المبادرات التسويقية عالية المخاطر فوراً',
                    'تشكيل فريق أزمات لمراقبة المخاطر أسبوعياً',
                    'وضع خطة بديلة لكل قناة تسويقية رئيسية',
                    'تخصيص احتياطي مالي للطوارئ (15-20% من الميزانية)',
                ]
            );
        } elseif ($overallRisk >= 50) {
            $recommendations[] = $this->formatRecommendation(
                'خطة تخفيف المخاطر',
                'يجب معالجة المخاطر المرتفعة وبناء آليات حماية',
                'high',
                [
                    'تحديد ومعالجة المخاطر الثلاث الأعلى أولوية',
                    'بناء نظام إنذار مبكر للمؤشرات الحرجة',
                    'تنويع القنوات التسويقية لتقليل الاعتماد على قناة واحدة',
                    'مراجعة شهرية لمستويات المخاطر',
                ]
            );
        } else {
            $recommendations[] = $this->formatRecommendation(
                'تعزيز إدارة المخاطر',
                'المخاطر ضمن النطاق المقبول مع فرص للتحسين',
                'medium',
                [
                    'توثيق سجل المخاطر وتحديثه ربع سنوياً',
                    'تدريب الفريق على إدارة المخاطر التسويقية',
                    'بناء مؤشرات أداء للإنذار المبكر',
                ]
            );
        }

        // Category-specific recommendations
        if (($categories['financial'] ?? 0) >= 60) {
            $recommendations[] = $this->formatRecommendation(
                'تخفيف المخاطر المالية',
                'المخاطر المالية مرتفعة وتحتاج ضبطاً فورياً',
                'high',
                [
                    'مراجعة العائد على الاستثمار لكل قناة تسويقية',
                    'وضع حدود إنفاق يومية/أسبوعية للحملات',
                    'ربط الميزانية بمؤشرات أداء واضحة',
                ]
            );
        }

        if (($categories['competitive'] ?? 0) >= 60) {
            $recommendations[] = $this->formatRecommendation(
                'مواجهة المخاطر التنافسية',
                'الموقف التنافسي ضعيف ويحتاج تعزيزاً',
                'high',
                [
                    'تحليل تنافسي شامل للمنافسين الرئيسيين',
                    'تحديد وتعزيز المزايا التنافسية الفريدة',
                    'بناء استراتيجية تمايز واضحة',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Analysis Helpers ────────────────────────────────────────

    private function assessRiskCategories(array $answers, array $context, array $scores): array {
        $risks = [];

        // Financial risk
        $revenueGrowth = $this->extractValue($answers, 'revenue_growth', 0);
        $profitMargin = $this->extractValue($answers, 'profit_margin', 0);
        $marketingROI = $this->extractValue($answers, 'marketing_roi', 0);
        $financialRisk = 50;
        if (is_numeric($revenueGrowth) && $revenueGrowth < 0) $financialRisk += 20;
        if (is_numeric($profitMargin) && $profitMargin < 10) $financialRisk += 15;
        if (is_numeric($marketingROI) && $marketingROI < 1) $financialRisk += 15;
        $risks['financial'] = min(100, $financialRisk);

        // Market risk
        $competitionLevel = $this->extractValue($answers, 'competition_level', 'medium');
        $marketGrowth = $this->extractValue($answers, 'market_growth', 'stable');
        $marketRisk = 40;
        if ($competitionLevel === 'very_high' || $competitionLevel === 'high') $marketRisk += 20;
        if ($marketGrowth === 'declining') $marketRisk += 25;
        if ($marketGrowth === 'stable') $marketRisk += 10;
        $risks['market'] = min(100, $marketRisk);

        // Operational risk
        $teamSize = $this->extractValue($answers, 'marketing_team_size', 1);
        $hasStrategy = $this->extractValue($answers, 'has_marketing_strategy', 'no');
        $operationalRisk = 40;
        if (is_numeric($teamSize) && $teamSize < 2) $operationalRisk += 20;
        if ($hasStrategy === 'no') $operationalRisk += 25;
        $risks['operational'] = min(100, $operationalRisk);

        // Competitive risk
        $marketPosition = $this->extractValue($answers, 'market_position', 'weak');
        $differentiation = $this->extractValue($answers, 'differentiation', 'low');
        $competitiveRisk = 40;
        if ($marketPosition === 'weak' || $marketPosition === 'follower') $competitiveRisk += 20;
        if ($differentiation === 'low' || $differentiation === 'none') $competitiveRisk += 20;
        $risks['competitive'] = min(100, $competitiveRisk);

        // Compliance risk
        $dataProtection = $this->extractValue($answers, 'data_protection', 'no');
        $complianceRisk = 30;
        if ($dataProtection === 'no') $complianceRisk += 25;
        $risks['compliance'] = min(100, $complianceRisk);

        return $risks;
    }

    private function calculateOverallRisk(array $riskScores): float {
        $weighted = 0;
        $totalWeight = 0;
        foreach (self::RISK_CATEGORIES as $cat => $config) {
            $score = $riskScores[$cat] ?? 50;
            $weighted += $score * $config['weight'];
            $totalWeight += $config['weight'];
        }
        return $totalWeight > 0 ? round($weighted / $totalWeight, 1) : 50;
    }

    private function getRiskLevel(float $score): array {
        foreach (self::RISK_LEVELS as $key => $level) {
            if ($score >= $level['min']) {
                return ['key' => $key, 'label' => $level['label'], 'color' => $level['color']];
            }
        }
        return ['key' => 'low', 'label' => 'منخفض', 'color' => 'success'];
    }

    private function identifyVulnerabilities(array $answers, array $context, array $riskScores): array {
        $vulnerabilities = [];

        if (($riskScores['financial'] ?? 0) >= 60) {
            $vulnerabilities[] = [
                'area'     => 'financial',
                'title'    => 'ضعف الوضع المالي التسويقي',
                'severity' => 'high',
                'detail'   => 'العوائد لا تغطي التكاليف التسويقية بشكل كافٍ',
            ];
        }

        if ($this->extractValue($answers, 'single_channel_dependency', 'no') === 'yes') {
            $vulnerabilities[] = [
                'area'     => 'operational',
                'title'    => 'الاعتماد على قناة تسويقية واحدة',
                'severity' => 'high',
                'detail'   => 'تعطل هذه القناة سيؤثر بشكل كبير على الأعمال',
            ];
        }

        if (($riskScores['competitive'] ?? 0) >= 60) {
            $vulnerabilities[] = [
                'area'     => 'competitive',
                'title'    => 'ضعف الموقف التنافسي',
                'severity' => 'medium',
                'detail'   => 'عدم وجود تمايز واضح عن المنافسين',
            ];
        }

        $hasWebsite = $this->extractValue($answers, 'has_website', 'no');
        if ($hasWebsite === 'no') {
            $vulnerabilities[] = [
                'area'     => 'digital',
                'title'    => 'غياب الحضور الرقمي',
                'severity' => 'high',
                'detail'   => 'عدم وجود موقع إلكتروني يضعف القدرة التنافسية',
            ];
        }

        if ($this->extractValue($answers, 'tracks_marketing_roi', 'no') === 'no') {
            $vulnerabilities[] = [
                'area'     => 'measurement',
                'title'    => 'غياب قياس الأداء',
                'severity' => 'medium',
                'detail'   => 'عدم تتبع عائد الاستثمار يمنع التحسين المستمر',
            ];
        }

        return $vulnerabilities;
    }

    private function calculateVulnerabilityIndex(array $vulnerabilities): float {
        if (empty($vulnerabilities)) return 0;

        $severityScores = ['critical' => 100, 'high' => 75, 'medium' => 50, 'low' => 25];
        $total = 0;
        foreach ($vulnerabilities as $v) {
            $total += $severityScores[$v['severity'] ?? 'medium'] ?? 50;
        }
        return min(100, round($total / count($vulnerabilities), 1));
    }

    private function buildMitigationPlan(array $riskScores, array $vulnerabilities): array {
        $plan = [];
        arsort($riskScores);

        $priority = 1;
        foreach ($riskScores as $category => $score) {
            if ($score >= 40) {
                $label = self::RISK_CATEGORIES[$category]['label'] ?? $category;
                $plan[] = [
                    'priority'  => $priority++,
                    'category'  => $category,
                    'label'     => $label,
                    'risk_score' => $score,
                    'strategy'  => $score >= 70 ? 'تخفيف فوري' : ($score >= 50 ? 'خطة تخفيف' : 'مراقبة وتحسين'),
                    'timeframe' => $score >= 70 ? '1-2 أسبوع' : ($score >= 50 ? '1-3 أشهر' : '3-6 أشهر'),
                ];
            }
        }

        return $plan;
    }
}
