<?php
/**
 * ContextEngine - محرك السياق والذاكرة
 * يدير سياق الجلسة ويتتبع حالة المحادثة ويستنتج معلومات العمل
 * Marketing AI System
 */
class ContextEngine {
    private Database $db;
    private array $sessionContext = [];

    // ثوابت حدود الاستنتاج
    private const CONFIDENCE_HIGH = 0.85;
    private const CONFIDENCE_MEDIUM = 0.65;
    private const CONFIDENCE_LOW = 0.40;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * تحميل سياق الجلسة من قاعدة البيانات
     * @param int $sessionId معرف الجلسة
     * @return array بيانات السياق
     */
    public function loadContext(int $sessionId): array {
        // جلب السياق المخزن في الجلسة
        $session = $this->db->fetch(
            "SELECT s.context, s.status, s.progress_percent, c.sector as company_sector,
                    c.years_in_business, c.employee_count, c.annual_revenue, c.monthly_costs
             FROM assessment_sessions s
             JOIN companies c ON s.company_id = c.id
             WHERE s.id = :id",
            ['id' => $sessionId]
        );

        if (!$session) {
            return [];
        }

        $context = json_decode($session['context'] ?? '{}', true) ?: [];

        // إضافة بيانات الشركة الأساسية إلى السياق
        $context['company_data'] = [
            'sector' => $session['company_sector'],
            'years_in_business' => $session['years_in_business'],
            'employee_count' => $session['employee_count'],
            'annual_revenue' => $session['annual_revenue'],
            'monthly_costs' => $session['monthly_costs'],
        ];

        $context['session_status'] = $session['status'];
        $context['progress'] = $session['progress_percent'];

        // تحميل الرؤى والتنبيهات المرتبطة
        $context['insights'] = $this->getInsights($sessionId);
        $context['flags'] = $this->getFlags($sessionId);

        $this->sessionContext[$sessionId] = $context;

        return $context;
    }

    /**
     * تحديث قيمة معينة في السياق
     * @param int $sessionId معرف الجلسة
     * @param string $key المفتاح
     * @param mixed $value القيمة
     * @param float $confidence مستوى الثقة (0-1)
     */
    public function updateContext(int $sessionId, string $key, mixed $value, float $confidence = 0.0): void {
        $context = $this->sessionContext[$sessionId] ?? $this->loadContext($sessionId);

        $context[$key] = [
            'value' => $value,
            'confidence' => $confidence,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->sessionContext[$sessionId] = $context;

        // حفظ في قاعدة البيانات
        $this->persistContext($sessionId, $context);
    }

    /**
     * استنتاج مرحلة العمل بناءً على الإجابات
     * startup < 2 سنة | early_growth 2-5 | growth 5-10 | mature 10-30 | legacy 30+
     * @param array $answers الإجابات
     * @return array ['value' => string, 'confidence' => float]
     */
    public function inferBusinessStage(array $answers): array {
        $years = $this->extractNumericAnswer($answers, 'years_in_business');

        if ($years === null) {
            // محاولة الاستنتاج من مؤشرات أخرى
            return $this->inferBusinessStageFromIndicators($answers);
        }

        $stage = match(true) {
            $years < 2   => 'startup',
            $years < 5   => 'early_growth',
            $years < 10  => 'growth',
            $years < 30  => 'mature',
            default       => 'legacy',
        };

        // ثقة عالية عند توفر البيانات المباشرة
        return [
            'value' => $stage,
            'confidence' => self::CONFIDENCE_HIGH,
            'source' => 'direct_answer',
            'years' => $years,
            'label_ar' => BUSINESS_STAGES[$stage]['ar'] ?? $stage,
        ];
    }

    /**
     * استنتاج مستوى الإلحاح
     * يعتمد على تراجع الإيرادات + قوة المنافسة + مستوى الرقمنة
     * @param array $answers الإجابات
     * @return array ['value' => string, 'confidence' => float]
     */
    public function inferUrgencyLevel(array $answers): array {
        $signals = [
            'revenue_declining' => $this->checkRevenueDecline($answers),
            'high_competition' => $this->checkHighCompetition($answers),
            'low_digital' => $this->checkLowDigitalPresence($answers),
            'high_churn' => $this->checkHighChurn($answers),
            'cash_flow_issues' => $this->checkCashFlowIssues($answers),
        ];

        $criticalCount = count(array_filter($signals));
        $totalSignals = count($signals);

        // حساب مستوى الإلحاح بناءً على عدد الإشارات الحرجة
        $urgency = match(true) {
            $criticalCount >= 4 => 'critical',
            $criticalCount >= 3 => 'high',
            $criticalCount >= 2 => 'medium',
            $criticalCount >= 1 => 'low',
            default              => 'minimal',
        };

        $confidence = $totalSignals > 0
            ? min(self::CONFIDENCE_HIGH, 0.5 + ($criticalCount / $totalSignals) * 0.4)
            : self::CONFIDENCE_LOW;

        return [
            'value' => $urgency,
            'confidence' => round($confidence, 2),
            'signals' => $signals,
            'critical_count' => $criticalCount,
            'label_ar' => $this->getUrgencyLabel($urgency),
        ];
    }

    /**
     * استنتاج فئة الميزانية
     * micro: 0-5000 | small: 5001-20000 | medium: 20001-100000 | large: 100001+
     * @param array $answers الإجابات
     * @return array ['value' => string, 'confidence' => float]
     */
    public function inferBudgetTier(array $answers): array {
        $budget = $this->extractNumericAnswer($answers, 'monthly_marketing_budget');

        if ($budget === null) {
            // محاولة الاستنتاج من الإيراد السنوي
            return $this->inferBudgetFromRevenue($answers);
        }

        $tier = match(true) {
            $budget <= 5000   => 'micro',
            $budget <= 20000  => 'small',
            $budget <= 100000 => 'medium',
            default           => 'large',
        };

        return [
            'value' => $tier,
            'confidence' => self::CONFIDENCE_HIGH,
            'source' => 'direct_answer',
            'monthly_budget' => $budget,
            'label_ar' => BUDGET_TIERS[$tier]['ar'] ?? $tier,
        ];
    }

    /**
     * استنتاج مستوى النضج الرقمي
     * يعتمد على: الموقع، السوشيال ميديا، الإعلان الرقمي، البريد، التحليلات
     * @param array $answers الإجابات
     * @return array ['value' => string, 'confidence' => float, 'score' => int]
     */
    public function inferDigitalMaturity(array $answers): array {
        $components = [
            'has_website' => $this->scoreDigitalComponent($answers, 'has_website', 25),
            'social_media_active' => $this->scoreDigitalComponent($answers, 'social_media_active', 20),
            'digital_advertising' => $this->scoreDigitalComponent($answers, 'digital_advertising', 20),
            'email_marketing' => $this->scoreDigitalComponent($answers, 'email_marketing', 15),
            'analytics_usage' => $this->scoreDigitalComponent($answers, 'analytics_usage', 20),
        ];

        $totalScore = array_sum(array_column($components, 'score'));
        $answeredCount = count(array_filter($components, fn($c) => $c['answered']));
        $totalComponents = count($components);

        // تحديد المستوى
        $level = match(true) {
            $totalScore <= 25 => 'beginner',
            $totalScore <= 50 => 'developing',
            $totalScore <= 75 => 'advanced',
            default           => 'expert',
        };

        $confidence = $totalComponents > 0
            ? round(($answeredCount / $totalComponents) * self::CONFIDENCE_HIGH, 2)
            : self::CONFIDENCE_LOW;

        return [
            'value' => $level,
            'confidence' => $confidence,
            'score' => $totalScore,
            'components' => $components,
            'label_ar' => MATURITY_LEVELS[$level]['ar'] ?? $level,
        ];
    }

    /**
     * استنتاج الوضع التنافسي في السوق
     * @param array $answers الإجابات
     * @return array ['value' => string, 'confidence' => float]
     */
    public function inferMarketPosition(array $answers): array {
        $indicators = [
            'market_share' => $this->extractNumericAnswer($answers, 'market_share'),
            'brand_awareness' => $this->extractNumericAnswer($answers, 'brand_awareness'),
            'customer_satisfaction' => $this->extractNumericAnswer($answers, 'customer_satisfaction'),
            'competitive_advantage' => $this->extractAnswer($answers, 'competitive_advantage'),
            'pricing_position' => $this->extractAnswer($answers, 'pricing_position'),
        ];

        $score = $this->calculateMarketPositionScore($indicators);

        $position = match(true) {
            $score >= 80 => 'market_leader',
            $score >= 60 => 'strong_competitor',
            $score >= 40 => 'average',
            $score >= 20 => 'weak',
            default      => 'struggling',
        };

        $answeredCount = count(array_filter($indicators, fn($v) => $v !== null));
        $confidence = $answeredCount > 0
            ? round(($answeredCount / count($indicators)) * self::CONFIDENCE_HIGH, 2)
            : self::CONFIDENCE_LOW;

        return [
            'value' => $position,
            'confidence' => $confidence,
            'score' => $score,
            'indicators' => $indicators,
            'label_ar' => $this->getMarketPositionLabel($position),
        ];
    }

    /**
     * الحصول على السياق الكامل مع جميع الاستنتاجات
     * @param int $sessionId معرف الجلسة
     * @return array السياق الكامل
     */
    public function getFullContext(int $sessionId): array {
        $context = $this->loadContext($sessionId);

        // جلب جميع الإجابات
        $answers = $this->db->fetchAll(
            "SELECT a.question_id, a.answer_value, a.answer_normalized
             FROM answers a
             WHERE a.session_id = :sid",
            ['sid' => $sessionId]
        );

        $answersMap = [];
        foreach ($answers as $a) {
            $answersMap[$a['question_id']] = $a['answer_value'];
        }

        // تشغيل جميع الاستنتاجات
        $context['inferences'] = [
            'business_stage' => $this->inferBusinessStage($answersMap),
            'urgency_level' => $this->inferUrgencyLevel($answersMap),
            'budget_tier' => $this->inferBudgetTier($answersMap),
            'digital_maturity' => $this->inferDigitalMaturity($answersMap),
            'market_position' => $this->inferMarketPosition($answersMap),
        ];

        // إضافة ملخص الثقة
        $context['confidence_summary'] = $this->calculateConfidenceSummary($context['inferences']);

        // إضافة الطابع الزمني
        $context['generated_at'] = date('Y-m-d H:i:s');

        return $context;
    }

    /**
     * إضافة رؤية/ملاحظة للجلسة
     * @param int $sessionId معرف الجلسة
     * @param string $insight نص الرؤية
     * @param string $source المصدر
     */
    public function addInsight(int $sessionId, string $insight, string $source): void {
        $this->db->insert('session_insights', [
            'session_id' => $sessionId,
            'insight_text' => $insight,
            'source' => $source,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * إضافة تنبيه/علم للجلسة
     * @param int $sessionId معرف الجلسة
     * @param string $flag نص التنبيه
     * @param string $severity مستوى الخطورة (critical, high, warning, info)
     */
    public function addFlag(int $sessionId, string $flag, string $severity): void {
        // التحقق من عدم تكرار نفس التنبيه
        $existing = $this->db->fetch(
            "SELECT id FROM session_flags WHERE session_id = :sid AND flag_text = :flag",
            ['sid' => $sessionId, 'flag' => $flag]
        );

        if ($existing) {
            // تحديث الخطورة إذا كانت أعلى
            $this->db->update('session_flags', [
                'severity' => $severity,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $existing['id']]);
            return;
        }

        $this->db->insert('session_flags', [
            'session_id' => $sessionId,
            'flag_text' => $flag,
            'severity' => $severity,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * جلب تنبيهات الجلسة
     * @param int $sessionId معرف الجلسة
     * @return array قائمة التنبيهات
     */
    public function getFlags(int $sessionId): array {
        return $this->db->fetchAll(
            "SELECT * FROM session_flags WHERE session_id = :sid ORDER BY
             FIELD(severity, 'critical', 'high', 'warning', 'info') ASC,
             created_at DESC",
            ['sid' => $sessionId]
        );
    }

    /**
     * جلب رؤى/ملاحظات الجلسة
     * @param int $sessionId معرف الجلسة
     * @return array قائمة الرؤى
     */
    public function getInsights(int $sessionId): array {
        return $this->db->fetchAll(
            "SELECT * FROM session_insights WHERE session_id = :sid ORDER BY created_at DESC",
            ['sid' => $sessionId]
        );
    }

    // ==================== الدوال المساعدة الخاصة ====================

    /**
     * حفظ السياق في قاعدة البيانات
     */
    private function persistContext(int $sessionId, array $context): void {
        // إزالة البيانات المؤقتة قبل الحفظ
        $persistable = $context;
        unset($persistable['insights'], $persistable['flags'], $persistable['company_data']);

        $this->db->query(
            "UPDATE assessment_sessions SET context = :ctx WHERE id = :id",
            ['ctx' => json_encode($persistable, JSON_UNESCAPED_UNICODE), 'id' => $sessionId]
        );
    }

    /**
     * استنتاج مرحلة العمل من مؤشرات غير مباشرة
     */
    private function inferBusinessStageFromIndicators(array $answers): array {
        $hasEmployees = $this->extractNumericAnswer($answers, 'employee_count');
        $revenue = $this->extractNumericAnswer($answers, 'annual_revenue');
        $hasWebsite = $this->extractAnswer($answers, 'has_website');

        // استنتاج تقريبي
        $stage = 'growth'; // افتراضي
        $confidence = self::CONFIDENCE_LOW;

        if ($revenue !== null) {
            $stage = match(true) {
                $revenue < 500000    => 'startup',
                $revenue < 2000000   => 'early_growth',
                $revenue < 10000000  => 'growth',
                $revenue < 50000000  => 'mature',
                default              => 'legacy',
            };
            $confidence = self::CONFIDENCE_MEDIUM;
        } elseif ($hasEmployees !== null) {
            $stage = match(true) {
                $hasEmployees <= 5   => 'startup',
                $hasEmployees <= 20  => 'early_growth',
                $hasEmployees <= 50  => 'growth',
                $hasEmployees <= 200 => 'mature',
                default              => 'legacy',
            };
            $confidence = self::CONFIDENCE_MEDIUM - 0.10;
        }

        return [
            'value' => $stage,
            'confidence' => $confidence,
            'source' => 'indirect_inference',
            'label_ar' => BUSINESS_STAGES[$stage]['ar'] ?? $stage,
        ];
    }

    /**
     * استنتاج الميزانية من الإيراد
     * القاعدة: عادة 5-15% من الإيراد يذهب للتسويق
     */
    private function inferBudgetFromRevenue(array $answers): array {
        $revenue = $this->extractNumericAnswer($answers, 'annual_revenue');

        if ($revenue === null) {
            return [
                'value' => 'unknown',
                'confidence' => 0.0,
                'source' => 'no_data',
                'label_ar' => 'غير محدد',
            ];
        }

        // تقدير: 8% من الإيراد السنوي / 12 شهر
        $estimatedMonthly = ($revenue * 0.08) / 12;

        $tier = match(true) {
            $estimatedMonthly <= 5000   => 'micro',
            $estimatedMonthly <= 20000  => 'small',
            $estimatedMonthly <= 100000 => 'medium',
            default                     => 'large',
        };

        return [
            'value' => $tier,
            'confidence' => self::CONFIDENCE_LOW,
            'source' => 'revenue_estimate',
            'estimated_monthly' => round($estimatedMonthly),
            'label_ar' => BUDGET_TIERS[$tier]['ar'] ?? $tier,
        ];
    }

    /**
     * التحقق من تراجع الإيرادات
     */
    private function checkRevenueDecline(array $answers): bool {
        $trend = $this->extractAnswer($answers, 'revenue_trend');
        if ($trend !== null) {
            return in_array($trend, ['declining', 'sharply_declining', 'متراجع', 'متراجع_بشدة']);
        }

        $growthRate = $this->extractNumericAnswer($answers, 'revenue_growth_rate');
        return $growthRate !== null && $growthRate < 0;
    }

    /**
     * التحقق من شدة المنافسة
     */
    private function checkHighCompetition(array $answers): bool {
        $level = $this->extractAnswer($answers, 'competition_level');
        if ($level !== null) {
            return in_array($level, ['very_high', 'high', 'عالية_جداً', 'عالية']);
        }

        $competitors = $this->extractNumericAnswer($answers, 'competitor_count');
        return $competitors !== null && $competitors > 10;
    }

    /**
     * التحقق من ضعف الحضور الرقمي
     */
    private function checkLowDigitalPresence(array $answers): bool {
        $hasWebsite = $this->extractAnswer($answers, 'has_website');
        $socialActive = $this->extractAnswer($answers, 'social_media_active');

        if ($hasWebsite === 'no' || $hasWebsite === 'لا') {
            return true;
        }
        if ($socialActive === 'no' || $socialActive === 'لا') {
            return true;
        }

        $digitalScore = $this->extractNumericAnswer($answers, 'digital_presence_rating');
        return $digitalScore !== null && $digitalScore <= 3;
    }

    /**
     * التحقق من معدل تسرب العملاء المرتفع
     */
    private function checkHighChurn(array $answers): bool {
        $churn = $this->extractNumericAnswer($answers, 'customer_churn_rate');
        return $churn !== null && $churn > 20;
    }

    /**
     * التحقق من مشاكل التدفق النقدي
     */
    private function checkCashFlowIssues(array $answers): bool {
        $cashFlow = $this->extractAnswer($answers, 'cash_flow_status');
        if ($cashFlow !== null) {
            return in_array($cashFlow, ['negative', 'critical', 'سلبي', 'حرج']);
        }

        $revenue = $this->extractNumericAnswer($answers, 'annual_revenue');
        $costs = $this->extractNumericAnswer($answers, 'monthly_costs');
        if ($revenue !== null && $costs !== null) {
            return ($costs * 12) >= ($revenue * 0.95);
        }

        return false;
    }

    /**
     * تقييم مكون رقمي
     */
    private function scoreDigitalComponent(array $answers, string $key, int $maxScore): array {
        $value = $this->extractAnswer($answers, $key);
        $numericValue = $this->extractNumericAnswer($answers, $key);

        if ($value === null && $numericValue === null) {
            return ['score' => 0, 'answered' => false, 'max' => $maxScore];
        }

        // تقييم بناءً على نوع الإجابة
        $score = 0;
        if ($numericValue !== null) {
            // تقييم بمقياس 1-10 → تحويل إلى نسبة من الحد الأقصى
            $score = (int) round(($numericValue / 10) * $maxScore);
        } elseif (in_array($value, ['yes', 'نعم', 'true', '1'])) {
            $score = (int) round($maxScore * 0.7); // وجود = 70% من الحد الأقصى
        } elseif (in_array($value, ['no', 'لا', 'false', '0'])) {
            $score = 0;
        }

        return [
            'score' => min($score, $maxScore),
            'answered' => true,
            'max' => $maxScore,
            'raw_value' => $value ?? $numericValue,
        ];
    }

    /**
     * حساب نقاط الموقع التنافسي
     */
    private function calculateMarketPositionScore(array $indicators): int {
        $score = 50; // نقطة البداية

        if ($indicators['market_share'] !== null) {
            $score += ($indicators['market_share'] - 10) * 1.5;
        }
        if ($indicators['brand_awareness'] !== null) {
            $score += ($indicators['brand_awareness'] - 5) * 3;
        }
        if ($indicators['customer_satisfaction'] !== null) {
            $score += ($indicators['customer_satisfaction'] - 5) * 4;
        }
        if ($indicators['competitive_advantage'] !== null) {
            $score += match($indicators['competitive_advantage']) {
                'strong', 'قوية'     => 15,
                'moderate', 'متوسطة' => 5,
                'weak', 'ضعيفة'      => -10,
                default              => 0,
            };
        }
        if ($indicators['pricing_position'] !== null) {
            $score += match($indicators['pricing_position']) {
                'premium', 'عالي'     => 10,
                'competitive', 'تنافسي' => 5,
                'low', 'منخفض'        => -5,
                default               => 0,
            };
        }

        return max(0, min(100, (int) round($score)));
    }

    /**
     * حساب ملخص الثقة
     */
    private function calculateConfidenceSummary(array $inferences): array {
        $confidences = [];
        foreach ($inferences as $key => $inference) {
            $confidences[$key] = $inference['confidence'] ?? 0;
        }

        $values = array_values($confidences);
        $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;

        return [
            'average' => round($avg, 2),
            'min' => count($values) > 0 ? round(min($values), 2) : 0,
            'max' => count($values) > 0 ? round(max($values), 2) : 0,
            'details' => $confidences,
            'reliable' => $avg >= self::CONFIDENCE_MEDIUM,
        ];
    }

    /**
     * استخراج إجابة نصية من مصفوفة الإجابات
     */
    private function extractAnswer(array $answers, string $questionId): ?string {
        $value = $answers[$questionId] ?? null;

        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value['answer_value'] ?? ($value['raw'] ?? null);
        }

        return (string) $value;
    }

    /**
     * استخراج إجابة رقمية
     */
    private function extractNumericAnswer(array $answers, string $questionId): ?float {
        $value = $this->extractAnswer($answers, $questionId);

        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * تسمية مستوى الإلحاح بالعربية
     */
    private function getUrgencyLabel(string $urgency): string {
        return match($urgency) {
            'critical' => 'حرج - يتطلب تدخل فوري',
            'high'     => 'عالي - يتطلب اهتمام عاجل',
            'medium'   => 'متوسط - يحتاج متابعة',
            'low'      => 'منخفض - وضع مستقر',
            'minimal'  => 'طبيعي - لا قلق',
            default    => $urgency,
        };
    }

    /**
     * تسمية الموقع التنافسي بالعربية
     */
    private function getMarketPositionLabel(string $position): string {
        return match($position) {
            'market_leader'      => 'رائد السوق',
            'strong_competitor'  => 'منافس قوي',
            'average'            => 'متوسط',
            'weak'               => 'ضعيف',
            'struggling'         => 'يعاني',
            default              => $position,
        };
    }
}
