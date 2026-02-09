<?php
/**
 * BrandStrategist - Brand & Content Strategy Expert
 *
 * Evaluates brand health including awareness, consistency, content quality,
 * positioning clarity, and visual identity. Provides strategic recommendations
 * for brand building and content optimization.
 */
class BrandStrategist extends ExpertBase {

    private const BRAND_PILLARS = [
        'brand_awareness'   => 0.25,
        'brand_consistency' => 0.20,
        'content_quality'   => 0.20,
        'brand_positioning' => 0.20,
        'visual_identity'   => 0.15,
    ];

    private const AWARENESS_LEVELS = [
        'dominant'   => ['label' => 'علامة مهيمنة', 'score' => 95],
        'well_known' => ['label' => 'علامة معروفة', 'score' => 80],
        'recognized' => ['label' => 'علامة مألوفة', 'score' => 60],
        'emerging'   => ['label' => 'علامة ناشئة', 'score' => 40],
        'unknown'    => ['label' => 'علامة غير معروفة', 'score' => 15],
    ];

    private const POSITIONING_TYPES = [
        'price_leader'      => 'قيادة السعر',
        'quality_leader'    => 'قيادة الجودة',
        'innovation_leader' => 'قيادة الابتكار',
        'service_leader'    => 'قيادة الخدمة',
        'niche_specialist'  => 'تخصص في نيش',
        'undefined'         => 'غير محدد',
    ];

    protected function initialize(): void {
        $this->id = 'brand_strategist';
        $this->name = 'خبير العلامة التجارية والمحتوى';
        $this->role = 'تقييم قوة العلامة التجارية وجودة المحتوى ووضوح التموضع وتقديم استراتيجيات البناء';
        $this->expertiseAreas = [
            'brand_strategy',
            'content_marketing',
            'brand_positioning',
            'visual_identity',
            'brand_communication',
        ];
        $this->personality = [
            'creative'        => 0.9,
            'strategic'       => 0.85,
            'detail_oriented' => 0.8,
            'trend_aware'     => 0.85,
            'storyteller'     => 0.9,
        ];
        $this->decisionWeight = 0.7;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $sector = $context['sector'] ?? 'general';
        $brandData = $this->extractBrandData($answers, $context);

        $brandAudit = $this->performBrandAudit($brandData, $sector);
        $contentAssessment = $this->assessContent($brandData);
        $positioningAnalysis = $this->analyzePositioning($brandData, $sector);

        $sections = [
            'brand_audit'          => $brandAudit,
            'content_assessment'   => $contentAssessment,
            'positioning_analysis' => $positioningAnalysis,
        ];

        $confidence = $this->calculateConfidence($brandData);

        $result = $this->buildResult(
            $sections,
            [
                'brand_strength'     => round($brandAudit['brand_score'], 1),
                'content_score'      => round($contentAssessment['content_score'], 1),
                'positioning_clarity'=> round($positioningAnalysis['clarity_score'], 1),
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

        $brandStrength = $scores['brand_strength'] ?? 50;
        $insights[] = $this->formatInsight(
            'قوة العلامة التجارية',
            sprintf('مستوى قوة العلامة التجارية: %s (%.0f/100)', $this->getScoreLabel($brandStrength), $brandStrength),
            $brandStrength >= 60 ? 'positive' : ($brandStrength >= 40 ? 'neutral' : 'negative'),
            0.88
        );

        $contentScore = $scores['content_score'] ?? 50;
        if ($contentScore < 40) {
            $insights[] = $this->formatInsight(
                'جودة المحتوى تحتاج تحسيناً',
                'المحتوى الحالي لا يرقى للمستوى المطلوب لبناء علاقة قوية مع الجمهور المستهدف',
                'warning',
                0.83
            );
        } elseif ($contentScore >= 75) {
            $insights[] = $this->formatInsight(
                'محتوى متميز',
                'جودة المحتوى عالية وتساهم بشكل فعال في بناء العلامة التجارية وجذب العملاء',
                'positive',
                0.85
            );
        }

        $positioningClarity = $scores['positioning_clarity'] ?? 50;
        if ($positioningClarity < 40) {
            $insights[] = $this->formatInsight(
                'غموض في التموضع',
                'التموضع في السوق غير واضح مما يضعف التمايز عن المنافسين ويشتت الرسائل التسويقية',
                'warning',
                0.85
            );
        }

        $consistency = $sections['brand_audit']['consistency_score'] ?? 50;
        if ($consistency < 50) {
            $insights[] = $this->formatInsight(
                'عدم اتساق في الهوية',
                'هناك تفاوت في تطبيق الهوية البصرية والرسائل التسويقية عبر القنوات المختلفة',
                'warning',
                0.80
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $scores = $analysisResult['scores'] ?? [];

        $brandStrength = $scores['brand_strength'] ?? 50;
        if ($brandStrength < 35) {
            $recommendations[] = $this->formatRecommendation(
                'إعادة بناء العلامة التجارية',
                'العلامة التجارية ضعيفة وتحتاج إعادة بناء شاملة لتحقيق تأثير في السوق',
                'critical',
                [
                    'تحديد القيم الجوهرية للعلامة التجارية ورسالتها',
                    'تطوير هوية بصرية متكاملة واحترافية',
                    'صياغة قصة العلامة التجارية بشكل مؤثر',
                    'وضع دليل للعلامة التجارية يضمن الاتساق في جميع نقاط التواصل',
                ]
            );
        } elseif ($brandStrength < 60) {
            $recommendations[] = $this->formatRecommendation(
                'تعزيز العلامة التجارية',
                'العلامة التجارية تحتاج تعزيزاً لزيادة الوعي والتمايز في السوق',
                'high',
                [
                    'زيادة نقاط التواصل مع الجمهور المستهدف',
                    'تحسين تجربة العميل لتعكس قيم العلامة التجارية',
                    'الاستثمار في حملات بناء الوعي بالعلامة التجارية',
                ]
            );
        }

        $contentScore = $scores['content_score'] ?? 50;
        if ($contentScore < 50) {
            $recommendations[] = $this->formatRecommendation(
                'تطوير استراتيجية المحتوى',
                'المحتوى الحالي يحتاج تطويراً جوهرياً ليخدم أهداف العلامة التجارية',
                'high',
                [
                    'وضع خطة محتوى شهرية متوافقة مع أهداف العلامة التجارية',
                    'تنويع أشكال المحتوى (مقالات، فيديو، إنفوجرافيك)',
                    'التركيز على المحتوى الذي يحل مشاكل العملاء',
                    'قياس أداء المحتوى وتحسينه بناءً على البيانات',
                ]
            );
        }

        $positioningClarity = $scores['positioning_clarity'] ?? 50;
        if ($positioningClarity < 50) {
            $recommendations[] = $this->formatRecommendation(
                'توضيح التموضع السوقي',
                'يجب تحديد تموضع واضح ومميز في السوق لتعزيز التنافسية',
                'medium',
                [
                    'تحليل تموضع المنافسين وتحديد الفجوات',
                    'صياغة عرض قيمة فريد واضح ومقنع',
                    'توحيد الرسائل التسويقية عبر جميع القنوات',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function extractBrandData(array $answers, array $context): array {
        return [
            'brand_awareness'   => $this->extractValue($answers, 'brand_awareness', 'unknown'),
            'brand_consistency' => $this->extractValue($answers, 'brand_consistency', 'low'),
            'content_quality'   => $this->extractValue($answers, 'content_quality', 'low'),
            'brand_positioning' => $this->extractValue($answers, 'brand_positioning', 'undefined'),
            'visual_identity'   => $this->extractValue($answers, 'visual_identity', 'weak'),
            'brand_voice'       => $this->extractValue($answers, 'brand_voice', 'undefined'),
            'content_frequency' => $this->extractValue($answers, 'content_frequency', 'rarely'),
            'sector'            => $context['sector'] ?? 'general',
        ];
    }

    private function performBrandAudit(array $data, string $sector): array {
        $awarenessInfo = self::AWARENESS_LEVELS[$data['brand_awareness']]
            ?? self::AWARENESS_LEVELS['unknown'];
        $awarenessScore = $awarenessInfo['score'];

        $consistencyScore = match ($data['brand_consistency']) {
            'excellent' => 90, 'high' => 75, 'moderate' => 55,
            'low' => 30, 'none' => 10, default => 35,
        };

        $visualScore = match ($data['visual_identity']) {
            'professional' => 90, 'good' => 70, 'basic' => 45,
            'weak' => 25, 'none' => 5, default => 30,
        };

        $brandScore = ($awarenessScore * self::BRAND_PILLARS['brand_awareness'])
                    + ($consistencyScore * self::BRAND_PILLARS['brand_consistency'])
                    + ($visualScore * self::BRAND_PILLARS['visual_identity'])
                    + ($this->getContentRawScore($data) * self::BRAND_PILLARS['content_quality'])
                    + ($this->getPositioningRawScore($data) * self::BRAND_PILLARS['brand_positioning']);

        return [
            'awareness_level'    => $awarenessInfo['label'],
            'awareness_score'    => $awarenessScore,
            'consistency_score'  => $consistencyScore,
            'visual_score'       => $visualScore,
            'brand_score'        => round($brandScore, 1),
            'brand_label'        => $this->getScoreLabel($brandScore),
        ];
    }

    private function assessContent(array $data): array {
        $qualityScore = match ($data['content_quality']) {
            'excellent' => 90, 'high' => 75, 'moderate' => 55,
            'low' => 30, 'none' => 5, default => 30,
        };

        $frequencyScore = match ($data['content_frequency']) {
            'daily' => 90, 'weekly' => 75, 'biweekly' => 60,
            'monthly' => 40, 'rarely' => 20, 'never' => 5, default => 20,
        };

        $voiceScore = match ($data['brand_voice']) {
            'distinctive' => 90, 'consistent' => 70, 'developing' => 45,
            'undefined' => 20, default => 25,
        };

        $contentScore = ($qualityScore * 0.45) + ($frequencyScore * 0.30) + ($voiceScore * 0.25);

        return [
            'quality_score'   => round($qualityScore, 1),
            'frequency_score' => round($frequencyScore, 1),
            'voice_score'     => round($voiceScore, 1),
            'content_score'   => round($contentScore, 1),
            'content_label'   => $this->getScoreLabel($contentScore),
        ];
    }

    private function analyzePositioning(array $data, string $sector): array {
        $positionType = self::POSITIONING_TYPES[$data['brand_positioning']]
            ?? self::POSITIONING_TYPES['undefined'];
        $positioningScore = $this->getPositioningRawScore($data);

        $awarenessInfo = self::AWARENESS_LEVELS[$data['brand_awareness']]
            ?? self::AWARENESS_LEVELS['unknown'];
        $clarityScore = ($positioningScore * 0.6) + ($awarenessInfo['score'] * 0.4);

        return [
            'position_type'    => $positionType,
            'positioning_score'=> round($positioningScore, 1),
            'clarity_score'    => round($clarityScore, 1),
            'clarity_label'    => $this->getScoreLabel($clarityScore),
        ];
    }

    private function getContentRawScore(array $data): float {
        return match ($data['content_quality']) {
            'excellent' => 90, 'high' => 75, 'moderate' => 55,
            'low' => 30, 'none' => 5, default => 30,
        };
    }

    private function getPositioningRawScore(array $data): float {
        return match ($data['brand_positioning']) {
            'price_leader', 'quality_leader', 'innovation_leader', 'service_leader' => 80,
            'niche_specialist' => 70,
            'undefined' => 15,
            default => 40,
        };
    }
}
