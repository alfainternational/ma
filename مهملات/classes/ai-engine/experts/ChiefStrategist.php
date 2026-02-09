<?php
/**
 * ChiefStrategist - Senior Strategic Expert
 *
 * Synthesizes inputs from all other experts to produce executive-level
 * strategic direction, plan classification, and priority setting.
 * Has the highest decision weight (1.0) and final authority.
 */
class ChiefStrategist extends ExpertBase {

    private const PLAN_TYPES = [
        'emergency'      => ['label' => 'خطة طوارئ', 'threshold' => 25],
        'treatment'      => ['label' => 'خطة علاجية', 'threshold' => 50],
        'growth'         => ['label' => 'خطة نمو', 'threshold' => 75],
        'transformation' => ['label' => 'خطة تحول', 'threshold' => 100],
    ];

    private const HEALTH_DIMENSIONS = [
        'financial'  => 0.25,
        'market'     => 0.20,
        'digital'    => 0.15,
        'brand'      => 0.15,
        'operations' => 0.10,
        'innovation' => 0.10,
        'risk'       => 0.05,
    ];

    protected function initialize(): void {
        $this->id = 'chief_strategist';
        $this->name = 'خبير الاستراتيجية الرئيسي';
        $this->role = 'المسؤول عن التوجيه الاستراتيجي العام وتحديد الأولويات وتنسيق مخرجات جميع الخبراء';
        $this->expertiseAreas = [
            'strategic_planning',
            'business_analysis',
            'executive_synthesis',
            'priority_setting',
            'resource_allocation',
        ];
        $this->personality = [
            'analytical'   => 0.9,
            'decisive'     => 0.95,
            'holistic'     => 0.9,
            'risk_aware'   => 0.8,
            'visionary'    => 0.85,
        ];
        $this->decisionWeight = 1.0;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $businessHealth = $this->assessBusinessHealth($scores);
        $planType = $this->determinePlanType($businessHealth['overall_score']);
        $strategicDirection = $this->determineStrategicDirection($answers, $context, $businessHealth);
        $prioritySetting = $this->setPriorities($answers, $context, $scores, $businessHealth);
        $executiveSummary = $this->buildExecutiveSummary($context, $businessHealth, $planType, $strategicDirection);

        $sections = [
            'executive_summary'    => $executiveSummary,
            'strategic_direction'  => $strategicDirection,
            'final_recommendations'=> $prioritySetting['recommendations'],
            'priority_setting'     => $prioritySetting['priorities'],
        ];

        $confidence = $this->calculateConfidence(array_merge($answers, $scores));

        $result = $this->buildResult(
            $sections,
            [
                'overall_health'   => $businessHealth['overall_score'],
                'dimension_scores' => $businessHealth['dimensions'],
                'plan_type'        => $planType,
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
        $overallScore = $analysisResult['scores']['overall_health'] ?? 0;
        $dimensions = $analysisResult['scores']['dimension_scores'] ?? [];

        // Overall health insight
        $insights[] = $this->formatInsight(
            'الصحة العامة للأعمال',
            sprintf('مستوى صحة الأعمال العام: %s (%.0f/100)', $this->getScoreLabel($overallScore), $overallScore),
            $overallScore >= 60 ? 'positive' : ($overallScore >= 40 ? 'neutral' : 'negative'),
            0.9
        );

        // Identify weakest dimensions
        if (!empty($dimensions)) {
            asort($dimensions);
            $weakest = array_slice($dimensions, 0, 2, true);
            foreach ($weakest as $dimension => $score) {
                if ($score < 50) {
                    $insights[] = $this->formatInsight(
                        "نقطة ضعف: {$dimension}",
                        sprintf('البعد "%s" يسجل %.0f/100 وهو أقل من المتوسط المطلوب ويحتاج اهتماماً فورياً', $dimension, $score),
                        'warning',
                        0.85
                    );
                }
            }

            // Identify strongest dimensions
            arsort($dimensions);
            $strongest = array_slice($dimensions, 0, 2, true);
            foreach ($strongest as $dimension => $score) {
                if ($score >= 70) {
                    $insights[] = $this->formatInsight(
                        "نقطة قوة: {$dimension}",
                        sprintf('البعد "%s" يسجل %.0f/100 ويمثل ميزة تنافسية يمكن البناء عليها', $dimension, $score),
                        'positive',
                        0.85
                    );
                }
            }
        }

        // Plan type insight
        $planType = $analysisResult['scores']['plan_type'] ?? [];
        if (!empty($planType)) {
            $insights[] = $this->formatInsight(
                'نوع الخطة الموصى بها',
                sprintf('بناءً على التحليل الشامل، الأعمال تحتاج إلى: %s', $planType['label'] ?? 'غير محدد'),
                $planType['key'] === 'emergency' ? 'negative' : 'neutral',
                0.9
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $overallScore = $analysisResult['scores']['overall_health'] ?? 0;
        $planType = $analysisResult['scores']['plan_type']['key'] ?? 'treatment';

        if ($planType === 'emergency') {
            $recommendations[] = $this->formatRecommendation(
                'خطة طوارئ عاجلة',
                'الوضع الحالي يتطلب تدخلاً فورياً لمعالجة المشكلات الأساسية قبل أي خطط توسعية',
                'critical',
                [
                    'تحديد وإيقاف جميع الأنشطة التسويقية غير المجدية فوراً',
                    'تركيز الموارد على القنوات الأعلى عائداً',
                    'وضع مؤشرات أداء أسبوعية للمتابعة',
                    'مراجعة هيكل التكاليف التسويقية بالكامل',
                ]
            );
        } elseif ($planType === 'treatment') {
            $recommendations[] = $this->formatRecommendation(
                'خطة علاجية شاملة',
                'يجب معالجة نقاط الضعف المحددة مع الحفاظ على العمليات الحالية الناجحة',
                'high',
                [
                    'معالجة نقاط الضعف المحددة في التحليل',
                    'تحسين العمليات التسويقية الحالية',
                    'بناء قدرات جديدة في المجالات الناقصة',
                    'وضع خطة تطوير للأشهر الثلاثة القادمة',
                ]
            );
        } elseif ($planType === 'growth') {
            $recommendations[] = $this->formatRecommendation(
                'خطة نمو مُسرّع',
                'الأعمال في وضع جيد للنمو مع فرص واضحة للتوسع',
                'medium',
                [
                    'زيادة الاستثمار في القنوات الأعلى أداءً',
                    'استكشاف أسواق وشرائح عملاء جديدة',
                    'تطوير شراكات استراتيجية',
                    'الاستثمار في الابتكار والتميز',
                ]
            );
        } else {
            $recommendations[] = $this->formatRecommendation(
                'خطة تحول استراتيجي',
                'الأعمال جاهزة للتحول النوعي والقيادة في السوق',
                'medium',
                [
                    'إعادة تعريف نموذج العمل للمرحلة القادمة',
                    'الاستثمار الكبير في التقنية والابتكار',
                    'بناء علامة تجارية رائدة في المجال',
                    'التوسع الجغرافي أو في فئات جديدة',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Analysis Helpers ────────────────────────────────────────

    private function assessBusinessHealth(array $scores): array {
        $dimensions = [];
        $weightedTotal = 0;
        $totalWeight = 0;

        foreach (self::HEALTH_DIMENSIONS as $dimension => $weight) {
            $dimensionScore = (float) ($scores[$dimension] ?? $scores["{$dimension}_score"] ?? 50);
            $dimensions[$dimension] = $dimensionScore;
            $weightedTotal += $dimensionScore * $weight;
            $totalWeight += $weight;
        }

        $overallScore = $totalWeight > 0 ? $weightedTotal / $totalWeight : 50;

        return [
            'overall_score' => round($overallScore, 1),
            'dimensions'    => $dimensions,
            'label'         => $this->getScoreLabel($overallScore),
        ];
    }

    private function determinePlanType(float $overallScore): array {
        foreach (self::PLAN_TYPES as $key => $plan) {
            if ($overallScore <= $plan['threshold']) {
                return [
                    'key'       => $key,
                    'label'     => $plan['label'],
                    'threshold' => $plan['threshold'],
                    'score'     => $overallScore,
                ];
            }
        }

        return [
            'key'       => 'transformation',
            'label'     => 'خطة تحول',
            'threshold' => 100,
            'score'     => $overallScore,
        ];
    }

    private function determineStrategicDirection(array $answers, array $context, array $health): array {
        $sector = $context['sector'] ?? 'general';
        $businessSize = $context['business_size'] ?? 'small';
        $overallScore = $health['overall_score'];

        $direction = [
            'primary_focus'    => $overallScore < 40 ? 'الإنقاذ والاستقرار' : ($overallScore < 70 ? 'التحسين والنمو' : 'التوسع والقيادة'),
            'time_horizon'     => $overallScore < 40 ? '3 أشهر' : ($overallScore < 70 ? '6 أشهر' : '12 شهراً'),
            'investment_level' => $this->determineInvestmentLevel($overallScore, $businessSize),
            'key_pillars'      => $this->identifyStrategicPillars($health['dimensions']),
            'sector'           => $sector,
        ];

        return $direction;
    }

    private function determineInvestmentLevel(float $score, string $size): string {
        if ($score < 40) {
            return 'محافظ - التركيز على الكفاءة';
        }
        if ($score < 70) {
            return match ($size) {
                'large'  => 'متوسط إلى مرتفع',
                'medium' => 'متوسط',
                default  => 'محدود ومركّز',
            };
        }
        return 'مرتفع - استثمار في النمو';
    }

    private function identifyStrategicPillars(array $dimensions): array {
        $pillars = [];
        asort($dimensions);

        $count = 0;
        foreach ($dimensions as $dimension => $score) {
            if ($score < 60 && $count < 3) {
                $pillars[] = [
                    'dimension'   => $dimension,
                    'current'     => $score,
                    'target'      => min(100, $score + 25),
                    'description' => "تحسين مستوى {$dimension} من {$score} إلى " . min(100, $score + 25),
                ];
                $count++;
            }
        }

        return $pillars;
    }

    private function setPriorities(array $answers, array $context, array $scores, array $health): array {
        $priorities = [];
        $recommendations = [];
        $dimensions = $health['dimensions'];

        asort($dimensions);
        $priority = 1;

        foreach ($dimensions as $dimension => $score) {
            if ($score < 70) {
                $priorities[] = [
                    'rank'       => $priority,
                    'dimension'  => $dimension,
                    'score'      => $score,
                    'urgency'    => $score < 40 ? 'عاجل' : ($score < 60 ? 'مهم' : 'تحسين'),
                    'timeframe'  => $score < 40 ? '0-30 يوماً' : ($score < 60 ? '1-3 أشهر' : '3-6 أشهر'),
                ];
                $priority++;
            }
        }

        return [
            'priorities'      => $priorities,
            'recommendations' => $recommendations,
        ];
    }

    private function buildExecutiveSummary(array $context, array $health, array $planType, array $direction): array {
        $companyName = $context['company_name'] ?? 'الشركة';
        $sector = $context['sector'] ?? 'عام';

        return [
            'company'         => $companyName,
            'sector'          => $sector,
            'overall_health'  => $health['overall_score'],
            'health_label'    => $health['label'],
            'plan_type'       => $planType['label'],
            'primary_focus'   => $direction['primary_focus'],
            'time_horizon'    => $direction['time_horizon'],
            'summary_text'    => sprintf(
                'بناءً على التحليل الشامل لأعمال %s في قطاع %s، سجلت الصحة العامة %.0f/100 (%s). الخطة الموصى بها: %s مع تركيز على %s خلال %s.',
                $companyName,
                $sector,
                $health['overall_score'],
                $health['label'],
                $planType['label'],
                $direction['primary_focus'],
                $direction['time_horizon']
            ),
        ];
    }
}
