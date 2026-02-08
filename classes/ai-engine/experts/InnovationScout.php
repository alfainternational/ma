<?php
/**
 * InnovationScout - Innovation & Opportunities Expert
 *
 * Identifies growth opportunities, emerging trends, market gaps,
 * and innovation potential for the business.
 */
class InnovationScout extends ExpertBase {

    private const INNOVATION_DIMENSIONS = [
        'technology_adoption'  => ['weight' => 0.25, 'label' => 'تبني التكنولوجيا'],
        'market_awareness'     => ['weight' => 0.20, 'label' => 'الوعي بالسوق'],
        'creative_capability'  => ['weight' => 0.20, 'label' => 'القدرة الإبداعية'],
        'adaptability'         => ['weight' => 0.20, 'label' => 'المرونة والتكيف'],
        'experimentation'      => ['weight' => 0.15, 'label' => 'التجريب والاختبار'],
    ];

    private const TREND_AREAS = [
        'ai_marketing'       => 'التسويق بالذكاء الاصطناعي',
        'video_content'      => 'محتوى الفيديو القصير',
        'social_commerce'    => 'التجارة الاجتماعية',
        'personalization'    => 'التخصيص والتفريد',
        'voice_search'       => 'البحث الصوتي',
        'sustainability'     => 'التسويق المستدام',
        'influencer_micro'   => 'المؤثرين الصغار',
        'community_building' => 'بناء المجتمعات',
    ];

    protected function initialize(): void {
        $this->id = 'innovation_scout';
        $this->name = 'خبير الفرص والابتكار';
        $this->role = 'اكتشاف فرص النمو والابتكار في التسويق وتحديد الاتجاهات الناشئة';
        $this->expertiseAreas = [
            'trend_analysis',
            'opportunity_identification',
            'innovation_assessment',
            'technology_scouting',
            'growth_hacking',
        ];
        $this->personality = [
            'creative'    => 0.95,
            'curious'     => 0.9,
            'optimistic'  => 0.85,
            'visionary'   => 0.9,
            'risk_tolerant' => 0.8,
        ];
        $this->decisionWeight = 0.6;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $innovationScores = $this->assessInnovationDimensions($answers, $context);
        $overallInnovation = $this->calculateOverallScore($innovationScores);
        $opportunities = $this->identifyOpportunities($answers, $context, $scores);
        $trendRelevance = $this->assessTrendRelevance($context);
        $techAssessment = $this->assessTechnologyReadiness($answers);

        $sections = [
            'innovation_audit'       => [
                'overall_score'    => $overallInnovation,
                'maturity_label'   => $this->getScoreLabel($overallInnovation),
                'dimension_scores' => $innovationScores,
            ],
            'opportunity_map'        => $opportunities,
            'technology_assessment'  => $techAssessment,
            'trend_relevance'        => $trendRelevance,
        ];

        $allScores = [
            'innovation_score'  => $overallInnovation,
            'opportunity_index' => $this->calculateOpportunityIndex($opportunities),
            'tech_readiness'    => $techAssessment['readiness_score'] ?? 40,
        ];

        $confidence = $this->calculateConfidence($answers);

        $result = $this->buildResult($sections, $allScores, [], [], $confidence);
        $result['insights'] = $this->generateInsights($result);
        $result['recommendations'] = $this->generateRecommendations($result);

        return $result;
    }

    public function generateInsights(array $analysisResult): array {
        $insights = [];
        $innovationScore = $analysisResult['scores']['innovation_score'] ?? 40;
        $opportunityIndex = $analysisResult['scores']['opportunity_index'] ?? 40;
        $opportunities = $analysisResult['sections']['opportunity_map'] ?? [];

        $insights[] = $this->formatInsight(
            'مستوى الابتكار التسويقي',
            sprintf('مستوى الابتكار: %s (%.0f/100). %s',
                $this->getScoreLabel($innovationScore),
                $innovationScore,
                $innovationScore >= 60 ? 'الشركة تتبنى الابتكار بشكل جيد' : 'هناك فرص كبيرة لتبني أساليب تسويقية مبتكرة'
            ),
            $innovationScore >= 60 ? 'positive' : 'neutral',
            0.8
        );

        if ($opportunityIndex >= 60) {
            $insights[] = $this->formatInsight(
                'فرص نمو عالية',
                sprintf('مؤشر الفرص %.0f/100 - هناك فرص نمو كبيرة غير مستغلة', $opportunityIndex),
                'positive',
                0.85
            );
        }

        // Top opportunities
        $topOpps = array_slice($opportunities, 0, 3);
        foreach ($topOpps as $opp) {
            $insights[] = $this->formatInsight(
                'فرصة: ' . ($opp['title'] ?? ''),
                $opp['description'] ?? '',
                'positive',
                $opp['confidence'] ?? 0.7
            );
        }

        // Trend insights
        $trends = $analysisResult['sections']['trend_relevance'] ?? [];
        $relevantTrends = array_filter($trends, fn($t) => ($t['relevance'] ?? 0) >= 70);
        if (!empty($relevantTrends)) {
            $trendNames = array_column(array_slice($relevantTrends, 0, 3), 'name');
            $insights[] = $this->formatInsight(
                'اتجاهات ذات صلة',
                'اتجاهات السوق الأكثر صلة بنشاطك: ' . implode('، ', $trendNames),
                'neutral',
                0.75
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $innovationScore = $analysisResult['scores']['innovation_score'] ?? 40;
        $techReadiness = $analysisResult['scores']['tech_readiness'] ?? 40;
        $opportunities = $analysisResult['sections']['opportunity_map'] ?? [];

        if ($innovationScore < 40) {
            $recommendations[] = $this->formatRecommendation(
                'بناء ثقافة الابتكار التسويقي',
                'مستوى الابتكار منخفض ويحتاج بناء أسس قوية للتجديد',
                'high',
                [
                    'تخصيص 10-15% من الميزانية التسويقية للتجريب',
                    'تبني أداة تسويقية جديدة كل شهر',
                    'متابعة 3 مصادر عربية للاتجاهات التسويقية',
                    'حضور ورشة عمل واحدة شهرياً في التسويق الرقمي',
                ]
            );
        } elseif ($innovationScore < 70) {
            $recommendations[] = $this->formatRecommendation(
                'تسريع الابتكار التسويقي',
                'أسس جيدة موجودة مع فرص للتسريع',
                'medium',
                [
                    'إطلاق حملة تجريبية شهرية على قناة جديدة',
                    'اختبار أدوات الذكاء الاصطناعي في إنشاء المحتوى',
                    'بناء شراكات مع مؤثرين في مجال جديد',
                    'تجربة تنسيقات محتوى مبتكرة (فيديو قصير، بودكاست)',
                ]
            );
        } else {
            $recommendations[] = $this->formatRecommendation(
                'قيادة الابتكار في القطاع',
                'مستوى ابتكار متقدم - حان وقت القيادة',
                'low',
                [
                    'مشاركة تجاربك الناجحة كمحتوى قيادة فكرية',
                    'استكشاف تقنيات الواقع المعزز في التسويق',
                    'بناء مجتمع رقمي حول علامتك التجارية',
                    'الاستثمار في التحليلات المتقدمة والتنبؤية',
                ]
            );
        }

        if ($techReadiness < 50) {
            $recommendations[] = $this->formatRecommendation(
                'تحديث البنية التقنية التسويقية',
                'البنية التقنية الحالية تحد من الابتكار',
                'high',
                [
                    'تقييم واختيار منصة أتمتة تسويق مناسبة',
                    'ربط قنوات التسويق بنظام CRM مركزي',
                    'تفعيل أدوات تحليل البيانات الأساسية',
                    'تدريب الفريق على الأدوات الرقمية الحديثة',
                ]
            );
        }

        // Top opportunities as recommendations
        foreach (array_slice($opportunities, 0, 2) as $opp) {
            if (($opp['potential_impact'] ?? 0) >= 60) {
                $recommendations[] = $this->formatRecommendation(
                    'استغلال فرصة: ' . ($opp['title'] ?? ''),
                    $opp['description'] ?? '',
                    'medium',
                    $opp['actions'] ?? ['دراسة الجدوى وتحديد الخطوات التالية']
                );
            }
        }

        return $recommendations;
    }

    // ─── Private Analysis Helpers ────────────────────────────────────────

    private function assessInnovationDimensions(array $answers, array $context): array {
        $scores = [];

        // Technology adoption
        $usesDigitalTools = $this->extractValue($answers, 'uses_digital_tools', 'no');
        $automationLevel = $this->extractValue($answers, 'automation_level', 1);
        $techScore = 30;
        if ($usesDigitalTools === 'yes') $techScore += 25;
        if (is_numeric($automationLevel)) $techScore += min(35, (int)$automationLevel * 7);
        $scores['technology_adoption'] = min(100, $techScore);

        // Market awareness
        $followsTrends = $this->extractValue($answers, 'follows_market_trends', 'no');
        $competitorMonitoring = $this->extractValue($answers, 'monitors_competitors', 'no');
        $marketScore = 30;
        if ($followsTrends === 'yes') $marketScore += 30;
        if ($competitorMonitoring === 'yes') $marketScore += 25;
        $scores['market_awareness'] = min(100, $marketScore);

        // Creative capability
        $contentCreation = $this->extractValue($answers, 'content_creation_frequency', 'rarely');
        $creativeScore = 30;
        if ($contentCreation === 'daily') $creativeScore += 40;
        elseif ($contentCreation === 'weekly') $creativeScore += 30;
        elseif ($contentCreation === 'monthly') $creativeScore += 15;
        $scores['creative_capability'] = min(100, $creativeScore);

        // Adaptability
        $pivotHistory = $this->extractValue($answers, 'has_pivoted', 'no');
        $adaptScore = 40;
        if ($pivotHistory === 'yes') $adaptScore += 25;
        $scores['adaptability'] = min(100, $adaptScore);

        // Experimentation
        $abTesting = $this->extractValue($answers, 'does_ab_testing', 'no');
        $triesNewChannels = $this->extractValue($answers, 'tries_new_channels', 'no');
        $expScore = 25;
        if ($abTesting === 'yes') $expScore += 30;
        if ($triesNewChannels === 'yes') $expScore += 25;
        $scores['experimentation'] = min(100, $expScore);

        return $scores;
    }

    private function calculateOverallScore(array $dimensionScores): float {
        $weighted = 0;
        $totalWeight = 0;
        foreach (self::INNOVATION_DIMENSIONS as $dim => $config) {
            $score = $dimensionScores[$dim] ?? 30;
            $weighted += $score * $config['weight'];
            $totalWeight += $config['weight'];
        }
        return $totalWeight > 0 ? round($weighted / $totalWeight, 1) : 30;
    }

    private function identifyOpportunities(array $answers, array $context, array $scores): array {
        $opportunities = [];
        $sector = $context['sector'] ?? 'general';

        // Check for digital transformation opportunity
        $digitalScore = $scores['digital_maturity'] ?? $scores['digital'] ?? 30;
        if ($digitalScore < 50) {
            $opportunities[] = [
                'id'               => 'OPP_DIGITAL',
                'title'            => 'التحول الرقمي',
                'description'      => 'فرصة كبيرة لتعزيز الحضور الرقمي وزيادة الوصول للعملاء',
                'potential_impact'  => 85,
                'effort'           => 'medium',
                'timeframe'        => '3-6 أشهر',
                'confidence'       => 0.85,
                'actions'          => [
                    'إنشاء أو تطوير الموقع الإلكتروني',
                    'تفعيل التواجد على منصات التواصل الاجتماعي',
                    'إطلاق حملات تسويق رقمي مستهدفة',
                ],
            ];
        }

        // Social commerce opportunity
        $hasSocialMedia = $this->extractValue($answers, 'has_social_media', 'no');
        $hasSocialSelling = $this->extractValue($answers, 'social_selling', 'no');
        if ($hasSocialMedia === 'yes' && $hasSocialSelling !== 'yes') {
            $opportunities[] = [
                'id'               => 'OPP_SOCIAL_COMMERCE',
                'title'            => 'التجارة عبر التواصل الاجتماعي',
                'description'      => 'تحويل المتابعين إلى عملاء من خلال البيع المباشر عبر المنصات',
                'potential_impact'  => 70,
                'effort'           => 'low',
                'timeframe'        => '1-2 شهر',
                'confidence'       => 0.8,
                'actions'          => [
                    'تفعيل خاصية المتجر على انستغرام',
                    'إنشاء كتالوج منتجات على واتساب بزنس',
                    'تصميم عروض حصرية لمتابعي المنصات',
                ],
            ];
        }

        // Content marketing opportunity
        $contentFreq = $this->extractValue($answers, 'content_creation_frequency', 'rarely');
        if ($contentFreq === 'rarely' || $contentFreq === 'never') {
            $opportunities[] = [
                'id'               => 'OPP_CONTENT',
                'title'            => 'التسويق بالمحتوى',
                'description'      => 'بناء محتوى قيّم يجذب العملاء ويبني الثقة والسلطة في المجال',
                'potential_impact'  => 75,
                'effort'           => 'medium',
                'timeframe'        => '2-4 أشهر',
                'confidence'       => 0.8,
                'actions'          => [
                    'إنشاء تقويم محتوى شهري',
                    'إنتاج محتوى تعليمي في مجال التخصص',
                    'الاستفادة من أدوات الذكاء الاصطناعي في إنشاء المحتوى',
                ],
            ];
        }

        // Customer loyalty program
        $hasLoyalty = $this->extractValue($answers, 'has_loyalty_program', 'no');
        $retention = $this->extractValue($answers, 'customer_retention', 0);
        if ($hasLoyalty === 'no' && is_numeric($retention) && $retention < 70) {
            $opportunities[] = [
                'id'               => 'OPP_LOYALTY',
                'title'            => 'برنامج ولاء العملاء',
                'description'      => 'بناء برنامج ولاء لزيادة الاحتفاظ بالعملاء وتكرار الشراء',
                'potential_impact'  => 65,
                'effort'           => 'medium',
                'timeframe'        => '2-3 أشهر',
                'confidence'       => 0.75,
                'actions'          => [
                    'تصميم نظام نقاط أو مكافآت بسيط',
                    'إطلاق عروض حصرية للعملاء المتكررين',
                    'استخدام واتساب للتواصل الشخصي مع العملاء المميزين',
                ],
            ];
        }

        // WhatsApp marketing (very relevant for Arab markets)
        $usesWhatsapp = $this->extractValue($answers, 'uses_whatsapp_business', 'no');
        if ($usesWhatsapp !== 'yes') {
            $opportunities[] = [
                'id'               => 'OPP_WHATSAPP',
                'title'            => 'تسويق واتساب بزنس',
                'description'      => 'استغلال واتساب كقناة تسويق ومبيعات رئيسية في السوق العربي',
                'potential_impact'  => 70,
                'effort'           => 'low',
                'timeframe'        => '2-4 أسابيع',
                'confidence'       => 0.85,
                'actions'          => [
                    'إعداد حساب واتساب بزنس احترافي',
                    'إنشاء كتالوج منتجات/خدمات',
                    'تفعيل الردود التلقائية وروبوت المحادثة',
                ],
            ];
        }

        // Sort by potential impact
        usort($opportunities, fn($a, $b) => ($b['potential_impact'] ?? 0) <=> ($a['potential_impact'] ?? 0));

        return $opportunities;
    }

    private function assessTrendRelevance(array $context): array {
        $sector = $context['sector'] ?? 'general';
        $trends = [];

        foreach (self::TREND_AREAS as $key => $name) {
            $relevance = match ($key) {
                'video_content'      => 80,
                'social_commerce'    => in_array($sector, ['retail', 'fnb', 'crafts']) ? 90 : 65,
                'personalization'    => in_array($sector, ['education', 'healthcare']) ? 85 : 70,
                'ai_marketing'       => 75,
                'influencer_micro'   => in_array($sector, ['fitness', 'fnb', 'crafts']) ? 85 : 60,
                'community_building' => in_array($sector, ['education', 'fitness']) ? 85 : 65,
                'voice_search'       => 50,
                'sustainability'     => in_array($sector, ['crafts', 'fnb']) ? 75 : 55,
                default              => 60,
            };

            $trends[] = [
                'key'       => $key,
                'name'      => $name,
                'relevance' => $relevance,
                'sector'    => $sector,
            ];
        }

        usort($trends, fn($a, $b) => $b['relevance'] <=> $a['relevance']);

        return $trends;
    }

    private function assessTechnologyReadiness(array $answers): array {
        $score = 30;

        if ($this->extractValue($answers, 'has_website', 'no') === 'yes') $score += 10;
        if ($this->extractValue($answers, 'uses_crm', 'no') === 'yes') $score += 15;
        if ($this->extractValue($answers, 'uses_analytics', 'no') === 'yes') $score += 15;
        if ($this->extractValue($answers, 'uses_automation', 'no') === 'yes') $score += 15;
        if ($this->extractValue($answers, 'uses_digital_tools', 'no') === 'yes') $score += 10;

        return [
            'readiness_score' => min(100, $score),
            'readiness_label' => $this->getScoreLabel(min(100, $score)),
        ];
    }

    private function calculateOpportunityIndex(array $opportunities): float {
        if (empty($opportunities)) return 30;

        $total = 0;
        foreach ($opportunities as $opp) {
            $total += $opp['potential_impact'] ?? 50;
        }
        return min(100, round($total / count($opportunities), 1));
    }
}
