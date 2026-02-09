<?php
/**
 * DataScientist - Data & Analytics Expert
 *
 * Evaluates data maturity, analytics capabilities, and data infrastructure.
 * Assesses data collection practices, quality, reporting frequency,
 * and the degree to which decisions are data-driven.
 */
class DataScientist extends ExpertBase {

    private const MATURITY_STAGES = [
        'optimized'   => ['label' => 'محسّن', 'min' => 80, 'score' => 95],
        'managed'     => ['label' => 'مُدار', 'min' => 60, 'score' => 75],
        'defined'     => ['label' => 'محدد', 'min' => 40, 'score' => 55],
        'developing'  => ['label' => 'قيد التطوير', 'min' => 20, 'score' => 35],
        'initial'     => ['label' => 'أولي', 'min' => 0, 'score' => 15],
    ];

    private const DIMENSION_WEIGHTS = [
        'data_collection'       => 0.20,
        'analytics_usage'       => 0.25,
        'data_quality'          => 0.20,
        'reporting_frequency'   => 0.15,
        'data_driven_decisions' => 0.20,
    ];

    private const ANALYTICS_LEVELS = [
        'predictive'   => ['label' => 'تحليلات تنبؤية', 'score' => 95],
        'diagnostic'   => ['label' => 'تحليلات تشخيصية', 'score' => 75],
        'descriptive'  => ['label' => 'تحليلات وصفية', 'score' => 55],
        'basic'        => ['label' => 'تحليلات أساسية', 'score' => 30],
        'none'         => ['label' => 'لا توجد تحليلات', 'score' => 5],
    ];

    protected function initialize(): void {
        $this->id = 'data_scientist';
        $this->name = 'عالم البيانات';
        $this->role = 'تقييم نضج البيانات والقدرات التحليلية والبنية التحتية للبيانات وتوجيه القرارات المبنية على البيانات';
        $this->expertiseAreas = [
            'data_analytics',
            'business_intelligence',
            'data_quality',
            'reporting',
            'data_strategy',
        ];
        $this->personality = [
            'analytical'      => 0.95,
            'methodical'      => 0.9,
            'detail_oriented' => 0.9,
            'curious'         => 0.85,
            'evidence_based'  => 0.95,
        ];
        $this->decisionWeight = 0.6;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $sector = $context['sector'] ?? 'general';
        $dataProfile = $this->extractDataProfile($answers, $context);

        $dataMaturity = $this->assessDataMaturity($dataProfile);
        $analyticsCapability = $this->evaluateAnalyticsCapability($dataProfile);
        $dataInfrastructure = $this->assessInfrastructure($dataProfile, $sector);

        $sections = [
            'data_maturity'        => $dataMaturity,
            'analytics_capability' => $analyticsCapability,
            'data_infrastructure'  => $dataInfrastructure,
        ];

        $confidence = $this->calculateConfidence($dataProfile);

        $result = $this->buildResult(
            $sections,
            [
                'data_readiness'     => round($dataMaturity['readiness_score'], 1),
                'analytics_maturity' => round($analyticsCapability['maturity_score'], 1),
                'insight_capability' => round($dataInfrastructure['insight_score'], 1),
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
        $scores = $analysisResult['scores'] ?? [];
        $sections = $analysisResult['sections'] ?? [];

        $readiness = $scores['data_readiness'] ?? 50;
        $insights[] = $this->formatInsight(
            'جاهزية البيانات',
            sprintf('مستوى جاهزية البيانات: %s (%.0f/100)', $this->getScoreLabel($readiness), $readiness),
            $readiness >= 60 ? 'positive' : ($readiness >= 40 ? 'neutral' : 'negative'),
            0.87
        );

        $analyticsMaturity = $scores['analytics_maturity'] ?? 50;
        if ($analyticsMaturity < 35) {
            $insights[] = $this->formatInsight(
                'قدرات تحليلية محدودة',
                'القدرات التحليلية الحالية أولية ولا تتيح استخلاص رؤى عميقة لدعم القرارات',
                'warning',
                0.85
            );
        } elseif ($analyticsMaturity >= 70) {
            $insights[] = $this->formatInsight(
                'قدرات تحليلية متقدمة',
                'القدرات التحليلية في مستوى متقدم تمكّن من استخلاص رؤى قيّمة ودعم القرارات بالبيانات',
                'positive',
                0.85
            );
        }

        $insightCapability = $scores['insight_capability'] ?? 50;
        if ($insightCapability < 40) {
            $insights[] = $this->formatInsight(
                'ضعف في استخلاص الرؤى',
                'البنية التحتية الحالية لا تدعم استخلاص رؤى فعّالة من البيانات المتاحة',
                'negative',
                0.82
            );
        }

        $dataQuality = $sections['data_maturity']['quality_score'] ?? 50;
        if ($dataQuality < 40) {
            $insights[] = $this->formatInsight(
                'جودة بيانات منخفضة',
                'جودة البيانات المتاحة منخفضة مما يقلل من موثوقية التحليلات والقرارات المبنية عليها',
                'warning',
                0.85
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $scores = $analysisResult['scores'] ?? [];

        $readiness = $scores['data_readiness'] ?? 50;
        if ($readiness < 35) {
            $recommendations[] = $this->formatRecommendation(
                'بناء أساس البيانات',
                'لا توجد بنية بيانات كافية ويجب البدء ببناء الأساس',
                'critical',
                [
                    'تحديد البيانات الأساسية المطلوبة لكل قناة تسويقية',
                    'تثبيت أدوات التتبع والتحليل (Google Analytics وما يعادلها)',
                    'إنشاء قاعدة بيانات مركزية للعملاء والحملات',
                    'تحديد مؤشرات الأداء الرئيسية وطريقة قياسها',
                ]
            );
        } elseif ($readiness < 60) {
            $recommendations[] = $this->formatRecommendation(
                'تطوير جمع البيانات وجودتها',
                'البيانات المتاحة تحتاج تحسيناً في الشمولية والجودة',
                'high',
                [
                    'توحيد مصادر البيانات في منصة مركزية',
                    'تحسين جودة البيانات من خلال التنظيف والتوحيد',
                    'أتمتة جمع البيانات من جميع نقاط التواصل',
                ]
            );
        }

        $analyticsMaturity = $scores['analytics_maturity'] ?? 50;
        if ($analyticsMaturity < 50) {
            $recommendations[] = $this->formatRecommendation(
                'تطوير القدرات التحليلية',
                'يجب رفع مستوى التحليلات للحصول على رؤى أعمق ودعم القرارات',
                'high',
                [
                    'الانتقال من التقارير الوصفية إلى التحليلات التشخيصية',
                    'تدريب الفريق على أدوات التحليل المتقدمة',
                    'بناء لوحات معلومات تفاعلية لمتابعة الأداء',
                    'وضع روتين أسبوعي لمراجعة البيانات واستخلاص الرؤى',
                ]
            );
        }

        $insightCapability = $scores['insight_capability'] ?? 50;
        if ($insightCapability >= 60) {
            $recommendations[] = $this->formatRecommendation(
                'الاستفادة من القدرات التحليلية المتقدمة',
                'القدرات الحالية تسمح بالانتقال للتحليلات التنبؤية',
                'medium',
                [
                    'تطبيق نماذج تنبؤية لسلوك العملاء',
                    'استخدام اختبارات A/B بشكل منهجي لتحسين الحملات',
                    'بناء نظام إنذار مبكر للكشف عن التغيرات في الأداء',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function extractDataProfile(array $answers, array $context): array {
        return [
            'data_collection'       => $this->extractValue($answers, 'data_collection', 'basic'),
            'analytics_usage'       => $this->extractValue($answers, 'analytics_usage', 'none'),
            'data_quality'          => $this->extractValue($answers, 'data_quality', 'low'),
            'reporting_frequency'   => $this->extractValue($answers, 'reporting_frequency', 'rarely'),
            'data_driven_decisions' => $this->extractValue($answers, 'data_driven_decisions', 'rarely'),
            'tools_used'            => $this->extractValue($answers, 'analytics_tools', []),
            'data_sources'          => (int) $this->extractValue($answers, 'data_sources', 1),
            'sector'                => $context['sector'] ?? 'general',
        ];
    }

    private function assessDataMaturity(array $data): array {
        $collectionScore = match ($data['data_collection']) {
            'comprehensive' => 90, 'systematic' => 70, 'moderate' => 50,
            'basic' => 30, 'minimal' => 15, 'none' => 5, default => 25,
        };

        $qualityScore = match ($data['data_quality']) {
            'excellent' => 90, 'high' => 75, 'moderate' => 55,
            'low' => 30, 'very_low' => 10, default => 30,
        };

        $decisionScore = match ($data['data_driven_decisions']) {
            'always' => 90, 'often' => 70, 'sometimes' => 50,
            'rarely' => 25, 'never' => 5, default => 25,
        };

        $readinessScore = ($collectionScore * 0.35) + ($qualityScore * 0.35) + ($decisionScore * 0.30);

        $maturityLabel = 'أولي';
        foreach (self::MATURITY_STAGES as $stage) {
            if ($readinessScore >= $stage['min']) {
                $maturityLabel = $stage['label'];
                break;
            }
        }

        return [
            'collection_score' => round($collectionScore, 1),
            'quality_score'    => round($qualityScore, 1),
            'decision_score'   => round($decisionScore, 1),
            'readiness_score'  => round($readinessScore, 1),
            'maturity_label'   => $maturityLabel,
        ];
    }

    private function evaluateAnalyticsCapability(array $data): array {
        $analyticsInfo = self::ANALYTICS_LEVELS[$data['analytics_usage']]
            ?? self::ANALYTICS_LEVELS['none'];

        $reportingScore = match ($data['reporting_frequency']) {
            'real_time' => 95, 'daily' => 85, 'weekly' => 65,
            'monthly' => 45, 'quarterly' => 25, 'rarely' => 10,
            'never' => 5, default => 20,
        };

        $sourceScore = min(100, $data['data_sources'] * 15);

        $maturityScore = ($analyticsInfo['score'] * 0.45) + ($reportingScore * 0.30) + ($sourceScore * 0.25);

        return [
            'analytics_level'  => $analyticsInfo['label'],
            'analytics_score'  => $analyticsInfo['score'],
            'reporting_score'  => round($reportingScore, 1),
            'source_score'     => round($sourceScore, 1),
            'maturity_score'   => round($maturityScore, 1),
            'maturity_label'   => $this->getScoreLabel($maturityScore),
        ];
    }

    private function assessInfrastructure(array $data, string $sector): array {
        $tools = is_array($data['tools_used']) ? $data['tools_used'] : [];
        $toolScore = min(100, count($tools) * 20);

        $collectionScore = match ($data['data_collection']) {
            'comprehensive' => 90, 'systematic' => 70, 'moderate' => 50,
            'basic' => 30, default => 15,
        };

        $analyticsInfo = self::ANALYTICS_LEVELS[$data['analytics_usage']]
            ?? self::ANALYTICS_LEVELS['none'];

        $insightScore = ($toolScore * 0.30) + ($collectionScore * 0.30) + ($analyticsInfo['score'] * 0.40);

        return [
            'tool_score'     => round($toolScore, 1),
            'tool_count'     => count($tools),
            'insight_score'  => round($insightScore, 1),
            'insight_label'  => $this->getScoreLabel($insightScore),
            'sector'         => $sector,
        ];
    }
}
