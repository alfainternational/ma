<?php
/**
 * DigitalMarketingExpert - Digital Marketing Analysis Expert
 *
 * Evaluates digital presence, channel performance, and digital strategy
 * maturity. Analyzes website traffic, social media, SEO, email marketing,
 * paid advertising, and content marketing effectiveness.
 */
class DigitalMarketingExpert extends ExpertBase {

    private const CHANNEL_WEIGHTS = [
        'website_traffic'    => 0.20,
        'social_media'       => 0.18,
        'seo'                => 0.18,
        'email_marketing'    => 0.15,
        'paid_advertising'   => 0.15,
        'content_marketing'  => 0.14,
    ];

    private const MATURITY_LEVELS = [
        'advanced'     => ['label' => 'متقدم', 'min' => 75],
        'intermediate' => ['label' => 'متوسط', 'min' => 50],
        'basic'        => ['label' => 'أساسي', 'min' => 25],
        'beginner'     => ['label' => 'مبتدئ', 'min' => 0],
    ];

    private const TRAFFIC_BENCHMARKS = [
        'excellent' => 50000,
        'good'      => 10000,
        'average'   => 3000,
        'low'       => 500,
    ];

    protected function initialize(): void {
        $this->id = 'digital_marketing_expert';
        $this->name = 'خبير التسويق الرقمي';
        $this->role = 'تحليل الحضور الرقمي وأداء القنوات الرقمية وتقييم نضج الاستراتيجية الرقمية';
        $this->expertiseAreas = [
            'digital_marketing',
            'seo_sem',
            'social_media_marketing',
            'email_marketing',
            'content_strategy',
            'paid_advertising',
        ];
        $this->personality = [
            'tech_savvy'      => 0.95,
            'data_driven'     => 0.9,
            'creative'        => 0.8,
            'trend_aware'     => 0.85,
            'results_focused' => 0.9,
        ];
        $this->decisionWeight = 0.8;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $sector = $context['sector'] ?? 'general';
        $digitalData = $this->extractDigitalData($answers, $context);

        $digitalPresence = $this->assessDigitalPresence($digitalData);
        $channelPerformance = $this->evaluateChannelPerformance($digitalData, $sector);
        $digitalStrategy = $this->assessDigitalStrategy($digitalData, $channelPerformance);

        $sections = [
            'digital_presence'    => $digitalPresence,
            'channel_performance' => $channelPerformance,
            'digital_strategy'    => $digitalStrategy,
        ];

        $confidence = $this->calculateConfidence($digitalData);

        $result = $this->buildResult(
            $sections,
            [
                'digital_maturity'      => round($digitalPresence['maturity_score'], 1),
                'channel_effectiveness' => round($channelPerformance['overall_score'], 1),
                'roi_potential'         => round($digitalStrategy['roi_potential'], 1),
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

        $maturity = $scores['digital_maturity'] ?? 50;
        $insights[] = $this->formatInsight(
            'نضج التسويق الرقمي',
            sprintf('مستوى النضج الرقمي: %s (%.0f/100)', $this->getScoreLabel($maturity), $maturity),
            $maturity >= 60 ? 'positive' : ($maturity >= 40 ? 'neutral' : 'negative'),
            0.88
        );

        $channelEffectiveness = $scores['channel_effectiveness'] ?? 50;
        if ($channelEffectiveness < 40) {
            $insights[] = $this->formatInsight(
                'أداء القنوات الرقمية دون المستوى',
                'أداء القنوات الرقمية أقل من المعدل المطلوب مما يعني ضياع فرص تسويقية كبيرة',
                'warning',
                0.85
            );
        }

        // Identify weakest and strongest channels
        $channels = $sections['channel_performance']['channels'] ?? [];
        if (!empty($channels)) {
            $channelScores = array_column($channels, 'score', 'name');
            if (!empty($channelScores)) {
                arsort($channelScores);
                $strongest = key($channelScores);
                $strongestScore = current($channelScores);
                if ($strongestScore >= 65) {
                    $insights[] = $this->formatInsight(
                        'قناة رقمية متميزة',
                        sprintf('قناة "%s" تحقق أداءً قوياً (%.0f/100) ويمكن زيادة الاستثمار فيها', $strongest, $strongestScore),
                        'positive',
                        0.82
                    );
                }

                asort($channelScores);
                $weakest = key($channelScores);
                $weakestScore = current($channelScores);
                if ($weakestScore < 40) {
                    $insights[] = $this->formatInsight(
                        'قناة رقمية تحتاج تطوير',
                        sprintf('قناة "%s" تسجل أداءً ضعيفاً (%.0f/100) وتحتاج تحسيناً أو إعادة تقييم', $weakest, $weakestScore),
                        'warning',
                        0.80
                    );
                }
            }
        }

        $roiPotential = $scores['roi_potential'] ?? 50;
        if ($roiPotential >= 70) {
            $insights[] = $this->formatInsight(
                'إمكانية عالية لتحسين العائد الرقمي',
                'هناك فرصة كبيرة لتحسين العائد على الاستثمار الرقمي من خلال تحسين الاستراتيجية الحالية',
                'positive',
                0.80
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $scores = $analysisResult['scores'] ?? [];

        $maturity = $scores['digital_maturity'] ?? 50;
        if ($maturity < 35) {
            $recommendations[] = $this->formatRecommendation(
                'بناء أساس رقمي متين',
                'مستوى النضج الرقمي منخفض ويحتاج بناء البنية التحتية الرقمية من الأساس',
                'critical',
                [
                    'تطوير موقع إلكتروني احترافي محسّن لمحركات البحث',
                    'إنشاء حسابات على المنصات الاجتماعية الرئيسية وتفعيلها',
                    'بناء قائمة بريدية وتفعيل التسويق عبر البريد الإلكتروني',
                    'تثبيت أدوات التحليل والمتابعة على جميع القنوات',
                ]
            );
        } elseif ($maturity < 60) {
            $recommendations[] = $this->formatRecommendation(
                'تطوير الاستراتيجية الرقمية',
                'هناك حاجة لتطوير الاستراتيجية الرقمية والانتقال للمرحلة المتقدمة',
                'high',
                [
                    'وضع استراتيجية محتوى رقمي متكاملة',
                    'تحسين محركات البحث بشكل منهجي',
                    'اختبار حملات إعلانية مدفوعة على المنصات الأعلى أداءً',
                    'أتمتة عمليات التسويق الرقمي الأساسية',
                ]
            );
        }

        $channelEffectiveness = $scores['channel_effectiveness'] ?? 50;
        if ($channelEffectiveness < 50) {
            $recommendations[] = $this->formatRecommendation(
                'تحسين أداء القنوات الرقمية',
                'أداء القنوات الحالية يحتاج تحسيناً لتعظيم العائد',
                'high',
                [
                    'تحليل أداء كل قناة وتحديد القنوات الأعلى عائداً',
                    'إعادة توزيع الميزانية نحو القنوات الأكثر فعالية',
                    'تحسين المحتوى والرسائل لكل قناة بشكل مخصص',
                    'وضع مؤشرات أداء واضحة لكل قناة ومراجعتها أسبوعياً',
                ]
            );
        }

        $roiPotential = $scores['roi_potential'] ?? 50;
        if ($roiPotential >= 60) {
            $recommendations[] = $this->formatRecommendation(
                'تعظيم العائد الرقمي',
                'هناك إمكانية لتحقيق عوائد أعلى من الاستثمار الرقمي الحالي',
                'medium',
                [
                    'زيادة الإنفاق على القنوات ذات العائد المثبت',
                    'تطبيق استراتيجيات إعادة الاستهداف',
                    'تحسين صفحات الهبوط ومعدلات التحويل',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function extractDigitalData(array $answers, array $context): array {
        return [
            'website_traffic'      => (int) $this->extractValue($answers, 'website_traffic', 0),
            'social_media_presence'=> $this->extractValue($answers, 'social_media_presence', 'weak'),
            'seo_score'            => (float) $this->extractValue($answers, 'seo_score', 0),
            'email_marketing'      => $this->extractValue($answers, 'email_marketing', 'none'),
            'paid_advertising'     => $this->extractValue($answers, 'paid_advertising', 'none'),
            'content_marketing'    => $this->extractValue($answers, 'content_marketing', 'none'),
            'conversion_rate'      => (float) $this->extractValue($answers, 'conversion_rate', 0),
            'social_followers'     => (int) $this->extractValue($answers, 'social_followers', 0),
            'email_list_size'      => (int) $this->extractValue($answers, 'email_list_size', 0),
            'sector'               => $context['sector'] ?? 'general',
        ];
    }

    private function assessDigitalPresence(array $data): array {
        $websiteScore = $this->calculateTrafficScore($data['website_traffic']);

        $socialScore = match ($data['social_media_presence']) {
            'excellent' => 90, 'strong' => 75, 'moderate' => 55,
            'weak' => 30, 'none' => 5, default => 30,
        };

        $seoScore = $this->normalizeScore($data['seo_score'], 0, 100);

        $maturityScore = ($websiteScore * 0.40) + ($socialScore * 0.30) + ($seoScore * 0.30);

        $maturityLevel = 'مبتدئ';
        foreach (self::MATURITY_LEVELS as $level) {
            if ($maturityScore >= $level['min']) {
                $maturityLevel = $level['label'];
                break;
            }
        }

        return [
            'website_score'   => round($websiteScore, 1),
            'social_score'    => round($socialScore, 1),
            'seo_score'       => round($seoScore, 1),
            'maturity_score'  => round($maturityScore, 1),
            'maturity_level'  => $maturityLevel,
        ];
    }

    private function evaluateChannelPerformance(array $data, string $sector): array {
        $channels = [];

        $channels[] = ['name' => 'الموقع الإلكتروني', 'score' => $this->calculateTrafficScore($data['website_traffic'])];

        $channels[] = ['name' => 'وسائل التواصل', 'score' => match ($data['social_media_presence']) {
            'excellent' => 90, 'strong' => 75, 'moderate' => 55, 'weak' => 30, default => 10,
        }];

        $channels[] = ['name' => 'تحسين محركات البحث', 'score' => $this->normalizeScore($data['seo_score'], 0, 100)];

        $channels[] = ['name' => 'التسويق بالبريد', 'score' => match ($data['email_marketing']) {
            'advanced' => 85, 'active' => 65, 'basic' => 40, 'occasional' => 20, default => 5,
        }];

        $channels[] = ['name' => 'الإعلانات المدفوعة', 'score' => match ($data['paid_advertising']) {
            'advanced' => 85, 'active' => 65, 'basic' => 40, 'occasional' => 20, default => 5,
        }];

        $channels[] = ['name' => 'تسويق المحتوى', 'score' => match ($data['content_marketing']) {
            'advanced' => 85, 'active' => 65, 'basic' => 40, 'occasional' => 20, default => 5,
        }];

        $totalScore = 0;
        $totalWeight = 0;
        $weights = array_values(self::CHANNEL_WEIGHTS);
        foreach ($channels as $i => $channel) {
            $w = $weights[$i] ?? 0.15;
            $totalScore += $channel['score'] * $w;
            $totalWeight += $w;
        }
        $overall = $totalWeight > 0 ? $totalScore / $totalWeight : 50;

        return [
            'channels'      => $channels,
            'overall_score' => round($overall, 1),
        ];
    }

    private function assessDigitalStrategy(array $data, array $channelPerf): array {
        $channelScore = $channelPerf['overall_score'];
        $conversionScore = $this->normalizeScore($data['conversion_rate'], 0, 10);

        $activeChannels = 0;
        foreach ($channelPerf['channels'] as $ch) {
            if ($ch['score'] >= 30) {
                $activeChannels++;
            }
        }
        $diversityScore = min(100, $activeChannels * 18);

        $roiPotential = ($channelScore * 0.4) + ($conversionScore * 0.3) + ($diversityScore * 0.3);

        return [
            'channel_score'    => round($channelScore, 1),
            'conversion_score' => round($conversionScore, 1),
            'diversity_score'  => round($diversityScore, 1),
            'active_channels'  => $activeChannels,
            'roi_potential'    => round($roiPotential, 1),
        ];
    }

    private function calculateTrafficScore(int $traffic): float {
        if ($traffic >= self::TRAFFIC_BENCHMARKS['excellent']) {
            return 90.0;
        }
        if ($traffic >= self::TRAFFIC_BENCHMARKS['good']) {
            return 70.0;
        }
        if ($traffic >= self::TRAFFIC_BENCHMARKS['average']) {
            return 50.0;
        }
        if ($traffic >= self::TRAFFIC_BENCHMARKS['low']) {
            return 30.0;
        }
        return max(5.0, $traffic / 50);
    }
}
