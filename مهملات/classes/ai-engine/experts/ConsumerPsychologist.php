<?php
/**
 * ConsumerPsychologist - Consumer Behavior Expert
 *
 * Analyzes customer satisfaction, retention patterns, loyalty metrics,
 * and lifetime value. Provides behavioral insights and recommendations
 * to improve customer relationships and reduce churn.
 */
class ConsumerPsychologist extends ExpertBase {

    private const SATISFACTION_LEVELS = [
        'very_high'  => ['label' => 'رضا ممتاز', 'score' => 95],
        'high'       => ['label' => 'رضا جيد', 'score' => 75],
        'moderate'   => ['label' => 'رضا متوسط', 'score' => 55],
        'low'        => ['label' => 'رضا منخفض', 'score' => 30],
        'very_low'   => ['label' => 'رضا ضعيف جداً', 'score' => 10],
    ];

    private const NPS_RANGES = [
        'promoter'  => ['min' => 50, 'label' => 'مروّجون'],
        'passive'   => ['min' => 0,  'label' => 'محايدون'],
        'detractor' => ['min' => -100, 'label' => 'منتقدون'],
    ];

    private const DIMENSION_WEIGHTS = [
        'customer_satisfaction'     => 0.25,
        'retention_rate'            => 0.25,
        'nps_score'                 => 0.20,
        'purchase_frequency'        => 0.15,
        'customer_lifetime_value'   => 0.15,
    ];

    protected function initialize(): void {
        $this->id = 'consumer_psychologist';
        $this->name = 'خبير سلوك المستهلك';
        $this->role = 'تحليل سلوك المستهلك ومستويات الرضا والولاء وتقديم رؤى لتحسين تجربة العميل';
        $this->expertiseAreas = [
            'consumer_behavior',
            'customer_satisfaction',
            'loyalty_programs',
            'retention_strategies',
            'customer_experience',
        ];
        $this->personality = [
            'empathetic'       => 0.9,
            'analytical'       => 0.85,
            'detail_oriented'  => 0.8,
            'psychology_driven'=> 0.9,
            'customer_centric' => 0.95,
        ];
        $this->decisionWeight = 0.7;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $sector = $context['sector'] ?? 'general';
        $customerData = $this->extractCustomerData($answers, $context);

        $behaviorAnalysis = $this->analyzeCustomerBehavior($customerData);
        $satisfactionAnalysis = $this->analyzeSatisfaction($customerData, $sector);
        $loyaltyAssessment = $this->assessLoyalty($customerData);

        $sections = [
            'customer_behavior'     => $behaviorAnalysis,
            'satisfaction_analysis' => $satisfactionAnalysis,
            'loyalty_assessment'    => $loyaltyAssessment,
        ];

        $healthScore = $this->calculateCustomerHealth($behaviorAnalysis, $satisfactionAnalysis, $loyaltyAssessment);
        $confidence = $this->calculateConfidence($customerData);

        $result = $this->buildResult(
            $sections,
            [
                'customer_health'    => round($healthScore, 1),
                'loyalty_score'      => round($loyaltyAssessment['loyalty_score'], 1),
                'satisfaction_index' => round($satisfactionAnalysis['satisfaction_score'], 1),
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

        $healthScore = $scores['customer_health'] ?? 50;
        $insights[] = $this->formatInsight(
            'صحة قاعدة العملاء',
            sprintf('مستوى صحة قاعدة العملاء: %s (%.0f/100)', $this->getScoreLabel($healthScore), $healthScore),
            $healthScore >= 60 ? 'positive' : ($healthScore >= 40 ? 'neutral' : 'negative'),
            0.88
        );

        $satisfactionIndex = $scores['satisfaction_index'] ?? 50;
        if ($satisfactionIndex < 40) {
            $insights[] = $this->formatInsight(
                'مستوى رضا العملاء منخفض',
                'رضا العملاء أقل من المعدل المقبول مما يزيد من مخاطر فقدان العملاء ويؤثر سلباً على السمعة',
                'warning',
                0.85
            );
        } elseif ($satisfactionIndex >= 75) {
            $insights[] = $this->formatInsight(
                'رضا عملاء متميز',
                'مستوى رضا العملاء ممتاز ويمثل ميزة تنافسية قوية يمكن استثمارها في برامج الإحالة',
                'positive',
                0.87
            );
        }

        $loyaltyScore = $scores['loyalty_score'] ?? 50;
        if ($loyaltyScore < 40) {
            $insights[] = $this->formatInsight(
                'ضعف ولاء العملاء',
                'مؤشرات الولاء تشير إلى ارتفاع مخاطر التسرب وضرورة تطوير برامج الاحتفاظ بالعملاء',
                'negative',
                0.83
            );
        }

        $retention = $sections['customer_behavior']['retention_rate'] ?? 0;
        if ($retention > 0 && $retention < 60) {
            $insights[] = $this->formatInsight(
                'معدل احتفاظ بالعملاء منخفض',
                sprintf('معدل الاحتفاظ بالعملاء (%.0f%%) أقل من المعدل الصحي، مما يرفع تكلفة اكتساب العملاء', $retention),
                'warning',
                0.85
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $scores = $analysisResult['scores'] ?? [];

        $satisfactionIndex = $scores['satisfaction_index'] ?? 50;
        if ($satisfactionIndex < 40) {
            $recommendations[] = $this->formatRecommendation(
                'برنامج تحسين رضا العملاء',
                'مستوى الرضا الحالي يتطلب تدخلاً عاجلاً لمنع فقدان العملاء',
                'critical',
                [
                    'إجراء استبيانات رضا العملاء بشكل دوري لتحديد نقاط الألم',
                    'تحسين خدمة ما بعد البيع وسرعة الاستجابة',
                    'إنشاء قنوات تواصل مباشرة مع العملاء',
                    'معالجة الشكاوى المتكررة بشكل جذري',
                ]
            );
        } elseif ($satisfactionIndex < 60) {
            $recommendations[] = $this->formatRecommendation(
                'تطوير تجربة العميل',
                'هناك فرصة لتحسين تجربة العميل وتعزيز مستويات الرضا',
                'high',
                [
                    'رسم خريطة رحلة العميل وتحديد نقاط الاحتكاك',
                    'تخصيص التواصل بناءً على سلوك العميل',
                    'تقديم محتوى قيّم يساعد العملاء في تحقيق أهدافهم',
                ]
            );
        }

        $loyaltyScore = $scores['loyalty_score'] ?? 50;
        if ($loyaltyScore < 50) {
            $recommendations[] = $this->formatRecommendation(
                'بناء برنامج ولاء فعّال',
                'ضعف الولاء يستوجب بناء برنامج منظم للاحتفاظ بالعملاء وتعزيز العلاقة',
                'high',
                [
                    'تصميم برنامج مكافآت يحفز تكرار الشراء',
                    'تقديم عروض حصرية للعملاء الحاليين',
                    'إنشاء مجتمع للعملاء يعزز الانتماء للعلامة التجارية',
                    'قياس ومتابعة مؤشر صافي الترويج (NPS) شهرياً',
                ]
            );
        }

        $healthScore = $scores['customer_health'] ?? 50;
        if ($healthScore >= 70) {
            $recommendations[] = $this->formatRecommendation(
                'استثمار قوة قاعدة العملاء',
                'قاعدة العملاء في حالة صحية جيدة ويمكن الاستفادة منها للنمو',
                'medium',
                [
                    'إطلاق برنامج إحالة يكافئ العملاء على جلب عملاء جدد',
                    'تطوير منتجات أو خدمات إضافية بناءً على احتياجات العملاء',
                    'جمع شهادات العملاء واستخدامها في التسويق',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function extractCustomerData(array $answers, array $context): array {
        return [
            'customer_satisfaction'   => $this->extractValue($answers, 'customer_satisfaction', 'moderate'),
            'retention_rate'          => (float) $this->extractValue($answers, 'retention_rate', 0),
            'nps_score'               => (float) $this->extractValue($answers, 'nps_score', 0),
            'purchase_frequency'      => $this->extractValue($answers, 'purchase_frequency', 'occasional'),
            'customer_lifetime_value' => (float) $this->extractValue($answers, 'customer_lifetime_value', 0),
            'churn_rate'              => (float) $this->extractValue($answers, 'churn_rate', 0),
            'repeat_purchase_rate'    => (float) $this->extractValue($answers, 'repeat_purchase_rate', 0),
            'sector'                  => $context['sector'] ?? 'general',
        ];
    }

    private function analyzeCustomerBehavior(array $data): array {
        $frequencyScore = match ($data['purchase_frequency']) {
            'daily' => 95, 'weekly' => 80, 'monthly' => 60,
            'quarterly' => 40, 'occasional' => 25, default => 30,
        };

        $retentionScore = $this->normalizeScore($data['retention_rate'], 0, 100);
        $repeatScore = $this->normalizeScore($data['repeat_purchase_rate'], 0, 100);

        $behaviorScore = ($frequencyScore * 0.35) + ($retentionScore * 0.40) + ($repeatScore * 0.25);

        return [
            'frequency_score'  => round($frequencyScore, 1),
            'retention_rate'   => $data['retention_rate'],
            'retention_score'  => round($retentionScore, 1),
            'repeat_rate'      => $data['repeat_purchase_rate'],
            'behavior_score'   => round($behaviorScore, 1),
            'behavior_label'   => $this->getScoreLabel($behaviorScore),
        ];
    }

    private function analyzeSatisfaction(array $data, string $sector): array {
        $level = self::SATISFACTION_LEVELS[$data['customer_satisfaction']]
            ?? self::SATISFACTION_LEVELS['moderate'];

        $npsScore = $this->normalizeScore($data['nps_score'], -100, 100);

        $npsCategory = 'منتقدون';
        foreach (self::NPS_RANGES as $category => $info) {
            if ($data['nps_score'] >= $info['min']) {
                $npsCategory = $info['label'];
                break;
            }
        }

        $satisfactionScore = ($level['score'] * 0.6) + ($npsScore * 0.4);

        return [
            'satisfaction_level' => $level['label'],
            'satisfaction_score' => round($satisfactionScore, 1),
            'nps_raw'            => $data['nps_score'],
            'nps_normalized'     => round($npsScore, 1),
            'nps_category'       => $npsCategory,
        ];
    }

    private function assessLoyalty(array $data): array {
        $retentionComponent = $this->normalizeScore($data['retention_rate'], 0, 100) * 0.35;
        $npsComponent = $this->normalizeScore($data['nps_score'], -100, 100) * 0.25;
        $ltvComponent = min(100, $data['customer_lifetime_value'] / 10) * 0.25;
        $churnPenalty = min(50, $data['churn_rate'] * 2) * 0.15;

        $loyaltyScore = $retentionComponent + $npsComponent + $ltvComponent - $churnPenalty;
        $loyaltyScore = max(0, min(100, $loyaltyScore));

        return [
            'loyalty_score' => round($loyaltyScore, 1),
            'loyalty_label' => $this->getScoreLabel($loyaltyScore),
            'churn_risk'    => $data['churn_rate'] > 20 ? 'مرتفع' : ($data['churn_rate'] > 10 ? 'متوسط' : 'منخفض'),
            'ltv'           => $data['customer_lifetime_value'],
        ];
    }

    private function calculateCustomerHealth(array $behavior, array $satisfaction, array $loyalty): float {
        return ($behavior['behavior_score'] * 0.30)
             + ($satisfaction['satisfaction_score'] * 0.35)
             + ($loyalty['loyalty_score'] * 0.35);
    }
}
