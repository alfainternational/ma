<?php
/**
 * MarketAnalyst - Market Analysis Expert
 *
 * Evaluates market dynamics including size, competition, growth potential,
 * and customer segmentation. Provides competitive landscape analysis
 * and identifies market opportunities with sector-specific benchmarks.
 */
class MarketAnalyst extends ExpertBase {

    private const COMPETITION_LEVELS = [
        'very_low'  => ['label' => 'منافسة ضعيفة جداً', 'score' => 90],
        'low'       => ['label' => 'منافسة ضعيفة', 'score' => 75],
        'moderate'  => ['label' => 'منافسة متوسطة', 'score' => 60],
        'high'      => ['label' => 'منافسة عالية', 'score' => 40],
        'very_high' => ['label' => 'منافسة شديدة', 'score' => 20],
    ];

    private const GROWTH_THRESHOLDS = [
        'rapid'    => ['min' => 20, 'label' => 'نمو سريع'],
        'moderate' => ['min' => 10, 'label' => 'نمو متوسط'],
        'slow'     => ['min' => 3,  'label' => 'نمو بطيء'],
        'stagnant' => ['min' => 0,  'label' => 'ركود'],
        'decline'  => ['min' => -100, 'label' => 'انكماش'],
    ];

    private const DIMENSION_WEIGHTS = [
        'market_size'        => 0.20,
        'competition_level'  => 0.25,
        'market_share'       => 0.20,
        'market_growth'      => 0.20,
        'customer_segments'  => 0.15,
    ];

    protected function initialize(): void {
        $this->id = 'market_analyst';
        $this->name = 'خبير تحليل السوق';
        $this->role = 'تحليل ديناميكيات السوق والمنافسة وتحديد الفرص والتهديدات وتقييم الموقع التنافسي';
        $this->expertiseAreas = [
            'market_analysis',
            'competitive_intelligence',
            'market_sizing',
            'customer_segmentation',
            'growth_forecasting',
        ];
        $this->personality = [
            'analytical'    => 0.9,
            'strategic'     => 0.85,
            'data_driven'   => 0.9,
            'forward_thinking' => 0.8,
            'detail_oriented'  => 0.85,
        ];
        $this->decisionWeight = 0.8;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $sector = $context['sector'] ?? 'general';
        $marketData = $this->extractMarketData($answers, $context);

        $marketOverview = $this->assessMarketOverview($marketData, $sector);
        $competitiveLandscape = $this->analyzeCompetitiveLandscape($marketData);
        $marketOpportunities = $this->identifyOpportunities($marketData, $sector);

        $sections = [
            'market_overview'       => $marketOverview,
            'competitive_landscape' => $competitiveLandscape,
            'market_opportunities'  => $marketOpportunities,
        ];

        $attractiveness = $this->calculateAttractiveness($marketOverview, $competitiveLandscape);
        $competitivePosition = $competitiveLandscape['position_score'] ?? 50;
        $growthPotential = $marketOpportunities['growth_score'] ?? 50;

        $confidence = $this->calculateConfidence($marketData);

        $result = $this->buildResult(
            $sections,
            [
                'market_attractiveness' => round($attractiveness, 1),
                'competitive_position'  => round($competitivePosition, 1),
                'growth_potential'      => round($growthPotential, 1),
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

        $attractiveness = $scores['market_attractiveness'] ?? 50;
        $insights[] = $this->formatInsight(
            'جاذبية السوق',
            sprintf('مستوى جاذبية السوق: %s (%.0f/100)', $this->getScoreLabel($attractiveness), $attractiveness),
            $attractiveness >= 60 ? 'positive' : ($attractiveness >= 40 ? 'neutral' : 'negative'),
            0.88
        );

        $competitivePosition = $scores['competitive_position'] ?? 50;
        $insights[] = $this->formatInsight(
            'الموقع التنافسي',
            sprintf('قوة الموقع التنافسي: %s (%.0f/100)', $this->getScoreLabel($competitivePosition), $competitivePosition),
            $competitivePosition >= 60 ? 'positive' : ($competitivePosition >= 40 ? 'neutral' : 'warning'),
            0.85
        );

        $growthPotential = $scores['growth_potential'] ?? 50;
        if ($growthPotential >= 70) {
            $insights[] = $this->formatInsight(
                'فرص نمو واعدة',
                'السوق يوفر فرص نمو كبيرة يمكن استغلالها لتعزيز الحصة السوقية',
                'positive',
                0.82
            );
        } elseif ($growthPotential < 35) {
            $insights[] = $this->formatInsight(
                'محدودية فرص النمو',
                'فرص النمو في السوق الحالي محدودة، يجب البحث عن أسواق أو شرائح جديدة',
                'warning',
                0.80
            );
        }

        $opportunities = $sections['market_opportunities']['items'] ?? [];
        if (count($opportunities) > 0) {
            $insights[] = $this->formatInsight(
                'فرص سوقية متاحة',
                sprintf('تم تحديد %d فرصة سوقية يمكن الاستفادة منها في الفترة القادمة', count($opportunities)),
                'positive',
                0.78
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $scores = $analysisResult['scores'] ?? [];

        $competitivePosition = $scores['competitive_position'] ?? 50;
        if ($competitivePosition < 40) {
            $recommendations[] = $this->formatRecommendation(
                'تعزيز الموقع التنافسي',
                'الموقع التنافسي ضعيف ويحتاج تدخلاً عاجلاً لتجنب فقدان الحصة السوقية',
                'critical',
                [
                    'تحليل نقاط القوة والضعف مقارنة بالمنافسين الرئيسيين',
                    'تطوير عرض قيمة فريد يميز العلامة التجارية',
                    'التركيز على شريحة سوقية محددة لبناء ريادة فيها',
                    'مراقبة تحركات المنافسين بشكل أسبوعي',
                ]
            );
        } elseif ($competitivePosition < 60) {
            $recommendations[] = $this->formatRecommendation(
                'تحسين التنافسية',
                'هناك فرصة لتحسين الموقع التنافسي والتقدم على المنافسين',
                'high',
                [
                    'تحديد الفجوات التنافسية والعمل على سدها',
                    'تعزيز نقاط التميز الحالية',
                    'بناء شراكات استراتيجية لتوسيع النطاق',
                ]
            );
        }

        $growthPotential = $scores['growth_potential'] ?? 50;
        if ($growthPotential >= 65) {
            $recommendations[] = $this->formatRecommendation(
                'استغلال فرص النمو',
                'السوق يوفر فرصاً جيدة للنمو يجب اقتناصها قبل المنافسين',
                'high',
                [
                    'زيادة الاستثمار في القنوات التسويقية الأعلى أداءً',
                    'التوسع في شرائح العملاء غير المخدومة',
                    'تطوير منتجات أو خدمات جديدة تلبي احتياجات السوق',
                    'بناء تحالفات لدخول أسواق جديدة',
                ]
            );
        }

        $attractiveness = $scores['market_attractiveness'] ?? 50;
        if ($attractiveness < 40) {
            $recommendations[] = $this->formatRecommendation(
                'إعادة تقييم السوق المستهدف',
                'جاذبية السوق الحالي منخفضة مما يستوجب دراسة بدائل أو تعديل الاستراتيجية',
                'medium',
                [
                    'دراسة أسواق مجاورة ذات جاذبية أعلى',
                    'تقييم إمكانية التخصص في نيش معين',
                    'مراجعة نموذج التسعير والقيمة المقدمة',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function extractMarketData(array $answers, array $context): array {
        return [
            'market_size'       => $this->extractValue($answers, 'market_size', 'medium'),
            'competition_level' => $this->extractValue($answers, 'competition_level', 'moderate'),
            'market_share'      => (float) $this->extractValue($answers, 'market_share', 0),
            'market_growth'     => (float) $this->extractValue($answers, 'market_growth', 0),
            'customer_segments' => (int) $this->extractValue($answers, 'customer_segments', 1),
            'competitors_count' => (int) $this->extractValue($answers, 'competitors_count', 5),
            'market_position'   => $this->extractValue($answers, 'market_position', 'follower'),
            'sector'            => $context['sector'] ?? 'general',
        ];
    }

    private function assessMarketOverview(array $data, string $sector): array {
        $sizeScore = match ($data['market_size']) {
            'very_large' => 90, 'large' => 75, 'medium' => 55,
            'small' => 35, 'very_small' => 15, default => 50,
        };

        $growthLabel = 'ركود';
        foreach (self::GROWTH_THRESHOLDS as $key => $info) {
            if ($data['market_growth'] >= $info['min']) {
                $growthLabel = $info['label'];
                break;
            }
        }

        $growthScore = $this->normalizeScore($data['market_growth'], -10, 30);

        return [
            'size_score'    => $sizeScore,
            'growth_rate'   => $data['market_growth'],
            'growth_label'  => $growthLabel,
            'growth_score'  => $growthScore,
            'segment_count' => $data['customer_segments'],
            'sector'        => $sector,
        ];
    }

    private function analyzeCompetitiveLandscape(array $data): array {
        $level = self::COMPETITION_LEVELS[$data['competition_level']] ?? self::COMPETITION_LEVELS['moderate'];

        $shareScore = $this->normalizeScore($data['market_share'], 0, 50);

        $positionScore = match ($data['market_position']) {
            'leader' => 90, 'challenger' => 70, 'follower' => 45, 'nicher' => 60, default => 40,
        };

        $overallPosition = ($shareScore * 0.4) + ($positionScore * 0.35) + ($level['score'] * 0.25);

        return [
            'competition_level'  => $level['label'],
            'competition_score'  => $level['score'],
            'market_share'       => $data['market_share'],
            'share_score'        => round($shareScore, 1),
            'position_label'     => $data['market_position'],
            'position_score'     => round($overallPosition, 1),
            'competitors_count'  => $data['competitors_count'],
        ];
    }

    private function identifyOpportunities(array $data, string $sector): array {
        $items = [];
        $growthScore = $this->normalizeScore($data['market_growth'], -10, 30);

        if ($data['market_growth'] > 10) {
            $items[] = ['title' => 'نمو سوقي مرتفع', 'description' => 'معدل نمو السوق يوفر فرصاً لزيادة الحصة'];
        }
        if ($data['customer_segments'] < 3) {
            $items[] = ['title' => 'تنويع الشرائح', 'description' => 'فرصة لاستهداف شرائح عملاء إضافية'];
        }
        if ($data['market_share'] < 15) {
            $items[] = ['title' => 'زيادة الحصة السوقية', 'description' => 'الحصة الحالية تسمح بمساحة كبيرة للنمو'];
        }

        $segmentScore = min(100, $data['customer_segments'] * 20);
        $overallGrowth = ($growthScore * 0.5) + ($segmentScore * 0.3) + (count($items) * 10 * 0.2);

        return [
            'items'        => $items,
            'growth_score' => round(min(100, $overallGrowth), 1),
            'sector'       => $sector,
        ];
    }

    private function calculateAttractiveness(array $overview, array $landscape): float {
        $sizeWeight = 0.3;
        $growthWeight = 0.35;
        $competitionWeight = 0.35;

        return ($overview['size_score'] * $sizeWeight)
             + ($overview['growth_score'] * $growthWeight)
             + ($landscape['competition_score'] * $competitionWeight);
    }
}
