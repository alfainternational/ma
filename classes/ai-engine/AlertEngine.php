<?php
/**
 * AlertEngine - محرك التنبيهات والإنذارات
 * نظام تنبيهات رباعي المستويات مع تنبيهات الفرص
 * Marketing AI System
 */
class AlertEngine {
    private Database $db;

    /** عتبات التنبيهات */
    private const REVENUE_DECLINE_CRITICAL = 20;
    private const RETENTION_LOW_THRESHOLD = 50;
    private const URGENCY_CRITICAL = 90;
    private const URGENCY_HIGH = 70;
    private const URGENCY_WARNING = 50;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * توليد جميع التنبيهات بناءً على التحليل الشامل
     * @param int $sessionId معرف الجلسة
     * @param array $scores نتائج التقييم
     * @param array $answers إجابات الاستبيان
     * @param array $expertResults نتائج تحليل الخبراء
     * @return array التنبيهات مرتبة حسب الأهمية
     */
    public function generateAlerts(int $sessionId, array $scores, array $answers, array $expertResults): array {
        $answersMap = $this->mapAnswers($answers);

        $critical = $this->checkCritical($scores, $answersMap);
        $high = $this->checkHigh($scores, $answersMap);
        $warning = $this->checkWarning($scores, $answersMap);
        $opportunities = $this->checkOpportunities($scores, $answersMap);

        $allAlerts = array_merge($critical, $high, $warning, $opportunities);

        // ترتيب حسب درجة الإلحاح
        usort($allAlerts, fn(array $a, array $b) => ($b['urgency_score'] ?? 0) <=> ($a['urgency_score'] ?? 0));

        $this->save($sessionId, $allAlerts);

        return [
            'alerts'    => $allAlerts,
            'critical'  => $critical,
            'high'      => $high,
            'warning'   => $warning,
            'opportunities' => $opportunities,
            'summary' => [
                'total'         => count($allAlerts),
                'critical_count' => count($critical),
                'high_count'     => count($high),
                'warning_count'  => count($warning),
                'opportunity_count' => count($opportunities),
                'max_urgency'    => !empty($allAlerts) ? (int)$allAlerts[0]['urgency_score'] : 0,
            ],
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * فحص التنبيهات الحرجة - مشاكل تهدد استمرارية الأعمال
     * @param array $scores نتائج التقييم
     * @param array $answers الإجابات
     * @return array تنبيهات حرجة
     */
    public function checkCritical(array $scores, array $answers): array {
        $alerts = [];
        $overall = (int)($scores['overall'] ?? 50);
        $riskScore = (int)($scores['risk_score'] ?? 50);

        // تراجع الإيرادات بأكثر من 20%
        $revTrend = $answers['revenue_trend'] ?? '';
        $revGrowth = (float)($answers['revenue_growth_rate'] ?? 0);
        if ($revTrend === 'declining' || $revTrend === 'sharply_declining' || $revGrowth < -self::REVENUE_DECLINE_CRITICAL) {
            $alerts[] = $this->buildAlert(
                'critical',
                'تراجع حاد في الإيرادات',
                'الإيرادات في انخفاض ملحوظ مما يهدد استمرارية الأعمال. يجب اتخاذ إجراءات فورية لوقف النزيف المالي',
                'financial',
                'مراجعة عاجلة لمصادر الإيرادات وتفعيل خطة طوارئ مالية',
                self::URGENCY_CRITICAL
            );
        }

        // غياب الحضور الرقمي في قطاع رقمي
        $sector = $answers['sector'] ?? '';
        $hasWebsite = in_array($answers['has_website'] ?? '', ['yes', 'نعم', '1', true], true);
        $digitalScore = (int)($scores['digital_maturity'] ?? 50);
        $digitalSectors = ['retail', 'fnb', 'fitness', 'education'];
        if (!$hasWebsite && $digitalScore < 20 && in_array($sector, $digitalSectors, true)) {
            $alerts[] = $this->buildAlert(
                'critical',
                'غياب كامل للحضور الرقمي في قطاع يتطلب ذلك',
                'قطاع ' . (SECTORS[$sector]['ar'] ?? $sector) . ' يعتمد بشكل كبير على التواجد الرقمي. الغياب الحالي يعني فقدان شريحة كبيرة من العملاء المحتملين',
                'digital_presence',
                'بناء موقع إلكتروني وحسابات تواصل اجتماعي خلال أسبوعين كحد أقصى',
                95
            );
        }

        // إنفاق تسويقي بدون تتبع
        $marketingBudget = (float)($answers['marketing_budget'] ?? 0);
        $tracksROI = in_array($answers['tracks_ad_roi'] ?? '', ['yes', 'نعم', '1', true], true);
        $usesAnalytics = in_array($answers['uses_analytics'] ?? '', ['yes', 'نعم', '1', true], true);
        if ($marketingBudget > 10000 && !$tracksROI && !$usesAnalytics) {
            $alerts[] = $this->buildAlert(
                'critical',
                'إنفاق تسويقي كبير بدون أي نظام تتبع أو قياس',
                'يتم إنفاق ميزانية تسويقية دون معرفة العائد الفعلي. هذا يعني احتمال هدر جزء كبير من الميزانية',
                'measurement',
                'تركيب أدوات تحليل وتتبع فوراً وربط جميع القنوات بنظام قياس موحد',
                92
            );
        }

        // درجة إجمالية حرجة مع مخاطر عالية
        if ($overall < 20 && $riskScore > 70) {
            $alerts[] = $this->buildAlert(
                'critical',
                'وضع تسويقي حرج مع مخاطر عالية',
                'الدرجة الإجمالية ' . $overall . '/100 مع مستوى مخاطر ' . $riskScore . '/100 يشير إلى وضع خطير يتطلب تدخلاً فورياً',
                'overall',
                'تفعيل خطة طوارئ شاملة وإعادة هيكلة الجهود التسويقية بالكامل',
                98
            );
        }

        return $alerts;
    }

    /**
     * فحص التنبيهات العالية - مشاكل مهمة تحتاج اهتماماً عاجلاً
     * @param array $scores نتائج التقييم
     * @param array $answers الإجابات
     * @return array تنبيهات عالية
     */
    public function checkHigh(array $scores, array $answers): array {
        $alerts = [];

        // تكلفة اكتساب العميل أعلى من قيمته
        $cac = (float)($answers['customer_acquisition_cost'] ?? 0);
        $ltv = (float)($answers['customer_lifetime_value'] ?? 0);
        if ($cac > 0 && $ltv > 0 && $cac > $ltv) {
            $ratio = round($cac / $ltv, 1);
            $alerts[] = $this->buildAlert(
                'high',
                'تكلفة اكتساب العميل أعلى من قيمته',
                'نسبة CAC/LTV تبلغ ' . $ratio . ' وهي أعلى من 1. كل عميل جديد يكلف أكثر مما يحقق من إيرادات',
                'financial',
                'تحسين كفاءة قنوات الاكتساب وزيادة قيمة العميل عبر برامج الولاء والبيع التكميلي',
                self::URGENCY_HIGH
            );
        }

        // معدل احتفاظ منخفض
        $retention = (float)($answers['customer_retention_rate'] ?? 100);
        $churnRate = (float)($answers['churn_rate'] ?? 0);
        if ($retention < self::RETENTION_LOW_THRESHOLD || $churnRate > 30) {
            $alerts[] = $this->buildAlert(
                'high',
                'معدل احتفاظ بالعملاء منخفض بشكل مقلق',
                'معدل الاحتفاظ بالعملاء ' . $retention . '% وهو أقل من الحد المقبول. فقدان العملاء الحاليين أكثر تكلفة من اكتساب عملاء جدد',
                'customers',
                'إطلاق برنامج ولاء فوري وتحسين تجربة ما بعد الشراء',
                75
            );
        }

        // غياب استراتيجية تسويقية
        $hasStrategy = in_array($answers['has_marketing_plan'] ?? '', ['yes', 'نعم', '1', true], true);
        $hasTargetAudience = in_array($answers['clear_target_audience'] ?? '', ['yes', 'نعم', '1', true], true);
        if (!$hasStrategy && !$hasTargetAudience) {
            $alerts[] = $this->buildAlert(
                'high',
                'غياب استراتيجية تسويقية واضحة',
                'لا توجد خطة تسويقية موثقة ولا تحديد واضح للجمهور المستهدف. العمل بدون استراتيجية يؤدي إلى هدر الموارد',
                'strategy',
                'وضع خطة تسويقية شاملة تتضمن تحديد الجمهور والأهداف والقنوات',
                72
            );
        }

        // ميزانية تسويقية غير متناسبة
        $annualRevenue = (float)($answers['annual_revenue'] ?? 0);
        $monthlyBudget = (float)($answers['marketing_budget'] ?? 0);
        if ($annualRevenue > 0 && $monthlyBudget > 0) {
            $budgetRatio = ($monthlyBudget * 12) / $annualRevenue;
            if ($budgetRatio > 0.30) {
                $alerts[] = $this->buildAlert(
                    'high',
                    'إنفاق تسويقي غير مستدام',
                    'الميزانية التسويقية تمثل ' . round($budgetRatio * 100, 1) . '% من الإيرادات وهي نسبة غير مستدامة',
                    'financial',
                    'إعادة هيكلة الميزانية التسويقية والتركيز على القنوات الأعلى عائداً',
                    self::URGENCY_HIGH
                );
            }
        }

        return $alerts;
    }

    /**
     * فحص تنبيهات التحذير - مناطق تحتاج انتباهاً
     * @param array $scores نتائج التقييم
     * @param array $answers الإجابات
     * @return array تنبيهات تحذيرية
     */
    public function checkWarning(array $scores, array $answers): array {
        $alerts = [];
        $overall = (int)($scores['overall'] ?? 50);
        $digitalMaturity = (int)($scores['digital_maturity'] ?? 50);
        $marketingMaturity = (int)($scores['marketing_maturity'] ?? 50);

        // أداء أقل من معيار القطاع
        if ($overall < 40) {
            $alerts[] = $this->buildAlert(
                'warning',
                'أداء تسويقي أقل من متوسط القطاع',
                'الدرجة الإجمالية ' . $overall . '/100 تضعك في المستوى الأدنى مقارنة بالمنافسين في القطاع',
                'overall',
                'وضع خطة تحسين مرحلية للوصول إلى متوسط القطاع خلال 6 أشهر',
                self::URGENCY_WARNING
            );
        }

        // علامة تجارية غير متسقة
        $consistentBranding = (float)($answers['consistent_branding'] ?? 5);
        $brandStrength = (float)($answers['brand_strength'] ?? 5);
        if ($consistentBranding < 4 || $brandStrength < 4) {
            $alerts[] = $this->buildAlert(
                'warning',
                'علامة تجارية غير متسقة أو ضعيفة',
                'عدم اتساق الهوية البصرية والرسائل التسويقية يُضعف التعرف على العلامة ويقلل الثقة',
                'brand',
                'توحيد الهوية البصرية وإنشاء دليل العلامة التجارية',
                55
            );
        }

        // تحليلات محدودة
        $tracksKPIs = in_array($answers['tracks_kpis'] ?? '', ['yes', 'نعم', '1', true], true);
        $regularReporting = in_array($answers['regular_reporting'] ?? '', ['yes', 'نعم', '1', true], true);
        if (!$tracksKPIs || !$regularReporting) {
            $alerts[] = $this->buildAlert(
                'warning',
                'قدرات تحليل وقياس محدودة',
                'غياب المتابعة المنتظمة لمؤشرات الأداء يمنع اتخاذ قرارات مبنية على بيانات',
                'analytics',
                'إنشاء لوحة متابعة لمؤشرات الأداء الرئيسية ومراجعتها أسبوعياً',
                52
            );
        }

        // فجوة رقمية
        if ($digitalMaturity < 40 && $marketingMaturity > 50) {
            $alerts[] = $this->buildAlert(
                'warning',
                'فجوة بين النضج التسويقي والرقمي',
                'لديك قدرات تسويقية جيدة لكن الحضور الرقمي متأخر. هذا يحد من الوصول للعملاء',
                'digital_presence',
                'الاستثمار في القنوات الرقمية لتتناسب مع مستوى النضج التسويقي',
                48
            );
        }

        // عدم وجود تميز واضح
        $differentiation = (float)($answers['differentiation'] ?? 5);
        $competitionLevel = $answers['competition_level'] ?? '';
        if ($differentiation < 4 && in_array($competitionLevel, ['high', 'very_high'], true)) {
            $alerts[] = $this->buildAlert(
                'warning',
                'ضعف التميز في سوق تنافسي',
                'مستوى التميز منخفض في بيئة تنافسية شديدة مما يصعّب اكتساب العملاء والاحتفاظ بهم',
                'competition',
                'تحديد وتعزيز عوامل التميز الفريدة وبناء مكانة واضحة في السوق',
                58
            );
        }

        return $alerts;
    }

    /**
     * فحص تنبيهات الفرص - مجالات إيجابية يمكن استغلالها
     * @param array $scores نتائج التقييم
     * @param array $answers الإجابات
     * @return array تنبيهات الفرص
     */
    public function checkOpportunities(array $scores, array $answers): array {
        $alerts = [];
        $opportunityScore = (int)($scores['opportunity_score'] ?? 50);

        // رضا عالي لكن إحالات منخفضة
        $satisfaction = (float)($answers['customer_satisfaction'] ?? 0);
        $referralRate = (float)($answers['referral_rate'] ?? 0);
        $nps = (float)($answers['nps_score'] ?? 0);
        if ($satisfaction > 7 && ($referralRate < 10 || $nps < 30)) {
            $alerts[] = $this->buildAlert(
                'opportunity',
                'فرصة: تحويل رضا العملاء إلى إحالات',
                'العملاء راضون بدرجة ' . $satisfaction . '/10 لكن معدل الإحالات منخفض. يمكن تحقيق نمو عضوي كبير',
                'customers',
                'إطلاق برنامج إحالات محفّز يستثمر رضا العملاء الحاليين',
                65
            );
        }

        // قنوات رقمية غير مستغلة
        $activeChannels = (int)($answers['active_platforms_count'] ?? 0);
        $usesGoogleAds = in_array($answers['uses_google_ads'] ?? '', ['yes', 'نعم', '1', true], true);
        $usesEmailMarketing = in_array($answers['email_campaigns'] ?? '', ['yes', 'نعم', '1', true], true);
        $unusedCount = 0;
        if (!$usesGoogleAds) {
            $unusedCount++;
        }
        if (!$usesEmailMarketing) {
            $unusedCount++;
        }
        if ($activeChannels < 3) {
            $unusedCount++;
        }
        if ($unusedCount >= 2) {
            $alerts[] = $this->buildAlert(
                'opportunity',
                'فرصة: قنوات رقمية غير مستغلة',
                'هناك قنوات تسويقية رقمية متاحة لم يتم استغلالها بعد وتمثل فرصة للوصول لشرائح عملاء جديدة',
                'digital_presence',
                'تجربة القنوات الجديدة بميزانية اختبارية وقياس النتائج خلال 30 يوماً',
                60
            );
        }

        // فجوات سوقية
        $marketGaps = (float)($answers['market_gaps'] ?? 0);
        $marketTrend = $answers['market_trend'] ?? '';
        if ($marketGaps > 6 || $marketTrend === 'growing') {
            $alerts[] = $this->buildAlert(
                'opportunity',
                'فرصة: فجوات سوقية واعدة',
                'السوق يشهد نمواً أو يحتوي على فجوات يمكن ملؤها. التحرك المبكر يمنح ميزة تنافسية',
                'market',
                'دراسة الفجوات السوقية وتطوير عروض مخصصة لسدها',
                58
            );
        }

        // منتج قوي مع تسويق ضعيف
        $productQuality = (float)($answers['product_quality'] ?? 5);
        $marketingMaturity = (int)($scores['marketing_maturity'] ?? 50);
        if ($productQuality > 7 && $marketingMaturity < 40) {
            $alerts[] = $this->buildAlert(
                'opportunity',
                'فرصة: منتج متميز يحتاج تسويقاً أفضل',
                'جودة المنتج/الخدمة عالية (' . $productQuality . '/10) لكن الجهود التسويقية لا تعكس ذلك. تحسين التسويق سيحقق نتائج سريعة',
                'marketing',
                'الاستثمار في تسويق احترافي يعكس جودة المنتج الفعلية',
                62
            );
        }

        return $alerts;
    }

    /**
     * حفظ التنبيهات في جدول analysis_results
     * @param int $sessionId معرف الجلسة
     * @param array $alerts التنبيهات
     */
    public function save(int $sessionId, array $alerts): void {
        $existing = $this->db->fetch(
            "SELECT id FROM analysis_results WHERE session_id = :sid",
            ['sid' => $sessionId]
        );

        $data = json_encode($alerts, JSON_UNESCAPED_UNICODE);

        if ($existing) {
            $this->db->update(
                'analysis_results',
                ['alerts' => $data, 'updated_at' => date('Y-m-d H:i:s')],
                'session_id = :sid',
                ['sid' => $sessionId]
            );
        } else {
            $this->db->insert('analysis_results', [
                'session_id'      => $sessionId,
                'alerts'          => $data,
                'scores'          => json_encode([]),
                'insights'        => json_encode([]),
                'expert_analysis' => json_encode([]),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ==================== الدوال المساعدة الخاصة ====================

    /**
     * بناء هيكل تنبيه موحد
     */
    private function buildAlert(
        string $type,
        string $title,
        string $description,
        string $dimension,
        string $recommendedAction,
        int $urgencyScore
    ): array {
        return [
            'type'               => $type,
            'title'              => $title,
            'description'        => $description,
            'dimension'          => $dimension,
            'recommended_action' => $recommendedAction,
            'urgency_score'      => max(0, min(100, $urgencyScore)),
            'created_at'         => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * تحويل مصفوفة الإجابات إلى خريطة مفاتيح
     */
    private function mapAnswers(array $answers): array {
        $map = [];
        foreach ($answers as $a) {
            if (is_array($a)) {
                $key = $a['question_id'] ?? $a['field_mapping'] ?? '';
                $map[$key] = $a['answer_value'] ?? null;
                if (isset($a['field_mapping'])) {
                    $map[$a['field_mapping']] = $a['answer_value'] ?? null;
                }
            }
        }
        return $map;
    }
}
