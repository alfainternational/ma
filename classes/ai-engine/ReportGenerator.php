<?php
/**
 * ReportGenerator - مولّد التقارير المتعددة الأنواع
 * يولّد 5 أنواع من التقارير التسويقية الشاملة
 * Marketing AI System
 */
class ReportGenerator {
    private Database $db;

    /** أنواع التقارير المدعومة */
    private const REPORT_TYPES = [
        'executive_summary'        => 'الملخص التنفيذي',
        'detailed_analysis'        => 'التحليل التفصيلي',
        'action_plan'              => 'خطة العمل',
        'monthly_performance'      => 'تقرير الأداء الشهري',
        'competitive_intelligence' => 'الاستخبارات التنافسية',
    ];

    /** الأبعاد التحليلية */
    private const ANALYSIS_DIMENSIONS = [
        'digital_maturity'          => 'النضج الرقمي',
        'marketing_maturity'        => 'النضج التسويقي',
        'organizational_readiness'  => 'الجاهزية المؤسسية',
        'risk_score'                => 'مستوى المخاطر',
        'opportunity_score'         => 'فرص النمو',
    ];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * توليد تقرير حسب النوع المطلوب
     * @param int $sessionId معرف الجلسة
     * @param string $reportType نوع التقرير
     * @return array التقرير الكامل
     */
    public function generate(int $sessionId, string $reportType): array {
        if (!array_key_exists($reportType, self::REPORT_TYPES)) {
            return [
                'error'   => true,
                'message' => 'نوع التقرير غير مدعوم: ' . $reportType,
                'supported_types' => self::REPORT_TYPES,
            ];
        }

        $report = match ($reportType) {
            'executive_summary'        => $this->generateExecutiveSummary($sessionId),
            'detailed_analysis'        => $this->generateDetailedAnalysis($sessionId),
            'action_plan'              => $this->generateActionPlan($sessionId),
            'monthly_performance'      => $this->generateMonthlyPerformance($sessionId),
            'competitive_intelligence' => $this->generateCompetitiveIntelligence($sessionId),
        };

        $reportId = $this->save($sessionId, $reportType, $report);
        $report['report_id'] = $reportId;

        return $report;
    }

    /**
     * الملخص التنفيذي - نظرة عامة للمديرين التنفيذيين
     * @param int $sessionId معرف الجلسة
     * @return array تقرير الملخص التنفيذي
     */
    public function generateExecutiveSummary(int $sessionId): array {
        $data = $this->loadSessionData($sessionId);
        $scores = $data['scores'];
        $company = $data['company'];
        $overall = (int)($scores['overall'] ?? 50);

        $sections = [];

        // القسم 1: نظرة عامة
        $sections[] = [
            'title' => 'نظرة عامة على الأداء التسويقي',
            'content' => [
                'company_name'   => $company['name'] ?? 'الشركة',
                'sector'         => SECTORS[$company['sector'] ?? '']['ar'] ?? ($company['sector'] ?? 'عام'),
                'overall_score'  => $overall,
                'maturity_level' => $scores['maturity_level'] ?? 'developing',
                'maturity_label' => MATURITY_LEVELS[$scores['maturity_level'] ?? 'developing']['ar'] ?? 'نامي',
                'summary_text'   => $this->buildOverallSummaryText($company, $overall),
            ],
        ];

        // القسم 2: أبرز النتائج
        $sections[] = [
            'title' => 'أبرز النتائج والمؤشرات',
            'content' => [
                'strengths'   => $this->extractStrengths($scores),
                'weaknesses'  => $this->extractWeaknesses($scores),
                'key_metrics' => $this->buildKeyMetrics($scores),
            ],
        ];

        // القسم 3: التوصيات الرئيسية
        $recommendations = $this->loadRecommendations($sessionId);
        $topRecs = array_slice($recommendations, 0, 5);
        $sections[] = [
            'title' => 'التوصيات ذات الأولوية القصوى',
            'content' => [
                'recommendations' => $topRecs,
                'next_steps'      => $this->buildNextSteps($overall),
            ],
        ];

        return $this->buildReport('الملخص التنفيذي', 'executive_summary', $sections, [
            'page_count' => 2,
            'audience'   => 'الإدارة التنفيذية',
        ]);
    }

    /**
     * التحليل التفصيلي - تحليل شامل لجميع الأبعاد
     * @param int $sessionId معرف الجلسة
     * @return array تقرير التحليل التفصيلي
     */
    public function generateDetailedAnalysis(int $sessionId): array {
        $data = $this->loadSessionData($sessionId);
        $scores = $data['scores'];
        $answers = $data['answers'];
        $sections = [];

        // القسم 1: ملخص الدرجات
        $sections[] = [
            'title' => 'ملخص التقييم الشامل',
            'content' => [
                'overall_score' => (int)($scores['overall'] ?? 50),
                'dimensions'    => $this->buildDimensionSummary($scores),
            ],
        ];

        // القسم 2-6: تحليل كل بعد
        foreach (self::ANALYSIS_DIMENSIONS as $key => $label) {
            $dimensionScore = (int)($scores[$key] ?? 50);
            $detailsKey = str_replace('_score', '', $key) . '_details';
            $details = $scores[$detailsKey] ?? $scores[str_replace('_score', '_breakdown', $key)] ?? [];

            $sections[] = [
                'title' => 'تحليل ' . $label,
                'content' => [
                    'score'          => $dimensionScore,
                    'level'          => $this->getScoreLevel($dimensionScore),
                    'level_label'    => $this->getScoreLevelLabel($dimensionScore),
                    'details'        => $details,
                    'analysis_text'  => $this->buildDimensionAnalysis($key, $dimensionScore, $details),
                    'recommendations' => $this->buildDimensionRecommendations($key, $dimensionScore),
                ],
            ];
        }

        // القسم 7: تحليل الفجوات
        $sections[] = [
            'title' => 'تحليل الفجوات',
            'content' => [
                'gaps'     => $this->identifyGaps($scores),
                'gap_text' => 'تم تحديد الفجوات بين الأداء الحالي والمستوى المطلوب لكل بعد',
            ],
        ];

        // القسم 8: التنبيهات
        $alerts = $this->loadAlerts($sessionId);
        $sections[] = [
            'title' => 'التنبيهات والإنذارات',
            'content' => [
                'alerts'         => $alerts,
                'critical_count' => count(array_filter($alerts, fn($a) => ($a['type'] ?? '') === 'critical')),
                'high_count'     => count(array_filter($alerts, fn($a) => ($a['type'] ?? '') === 'high')),
            ],
        ];

        return $this->buildReport('التحليل التفصيلي الشامل', 'detailed_analysis', $sections, [
            'page_count' => 8,
            'audience'   => 'فريق التسويق والإدارة',
        ]);
    }

    /**
     * خطة العمل - إجراءات مرتبة بجدول زمني
     * @param int $sessionId معرف الجلسة
     * @return array تقرير خطة العمل
     */
    public function generateActionPlan(int $sessionId): array {
        $data = $this->loadSessionData($sessionId);
        $scores = $data['scores'];
        $overall = (int)($scores['overall'] ?? 50);
        $recommendations = $this->loadRecommendations($sessionId);
        $sections = [];

        // القسم 1: ملخص الخطة
        $planType = $this->determinePlanType($overall, (int)($scores['risk_score'] ?? 50));
        $sections[] = [
            'title' => 'ملخص خطة العمل',
            'content' => [
                'plan_type'    => PLAN_TYPES[$planType . '_plan']['ar'] ?? $planType,
                'duration'     => PLAN_TYPES[$planType . '_plan']['duration'] ?? '6 أشهر',
                'total_actions' => count($recommendations),
                'objective'    => $this->getPlanObjective($planType),
            ],
        ];

        // القسم 2: الإجراءات الفورية (أول 30 يوم)
        $immediate = array_filter($recommendations, fn($r) => ($r['priority'] ?? '') === 'critical' || ($r['priority'] ?? '') === 'high');
        $sections[] = [
            'title' => 'إجراءات فورية - أول 30 يوماً',
            'content' => [
                'actions'  => array_values(array_slice($immediate, 0, 5)),
                'kpis'     => $this->buildPhaseKPIs('immediate'),
            ],
        ];

        // القسم 3: المرحلة الأولى (شهر 1-3)
        $phaseOne = array_filter($recommendations, fn($r) => ($r['layer'] ?? '') === 'tactical');
        $sections[] = [
            'title' => 'المرحلة الأولى - الأشهر 1 إلى 3',
            'content' => [
                'actions' => array_values(array_slice($phaseOne, 0, 5)),
                'kpis'    => $this->buildPhaseKPIs('phase_one'),
                'budget_allocation' => 'تخصيص 40% من الميزانية للتأسيس والبناء',
            ],
        ];

        // القسم 4: المرحلة الثانية (شهر 4-6)
        $sections[] = [
            'title' => 'المرحلة الثانية - الأشهر 4 إلى 6',
            'content' => [
                'actions' => [
                    'تقييم نتائج المرحلة الأولى وتعديل الخطة',
                    'توسيع القنوات الناجحة وإيقاف غير الفعالة',
                    'تعميق التحليلات وتحسين القرارات',
                    'بناء شراكات جديدة واستكشاف فرص النمو',
                ],
                'kpis'    => $this->buildPhaseKPIs('phase_two'),
                'budget_allocation' => 'تخصيص 35% من الميزانية للتوسع والتحسين',
            ],
        ];

        // القسم 5: المتابعة والقياس
        $sections[] = [
            'title' => 'إطار المتابعة والقياس',
            'content' => [
                'review_frequency' => 'مراجعة أسبوعية للمؤشرات الرئيسية',
                'reporting_cycle'  => 'تقرير شهري شامل للإدارة',
                'success_criteria' => $this->buildSuccessCriteria($overall),
                'escalation_rules' => [
                    'تصعيد فوري عند انخفاض أي مؤشر بأكثر من 10%',
                    'مراجعة طارئة عند ظهور تنبيهات حرجة جديدة',
                    'تعديل الخطة عند تغير ظروف السوق',
                ],
            ],
        ];

        return $this->buildReport('خطة العمل التنفيذية', 'action_plan', $sections, [
            'page_count' => 5,
            'audience'   => 'فريق التنفيذ والإدارة',
        ]);
    }

    /**
     * تقرير الأداء الشهري - قالب متابعة المؤشرات
     * @param int $sessionId معرف الجلسة
     * @return array قالب تقرير الأداء الشهري
     */
    public function generateMonthlyPerformance(int $sessionId): array {
        $data = $this->loadSessionData($sessionId);
        $scores = $data['scores'];
        $sections = [];

        // القسم 1: ملخص الأداء
        $sections[] = [
            'title' => 'ملخص الأداء لهذا الشهر',
            'content' => [
                'period'        => date('Y-m'),
                'baseline_score' => (int)($scores['overall'] ?? 50),
                'target_score'  => min(100, (int)($scores['overall'] ?? 50) + 5),
                'kpi_template'  => [
                    ['name' => 'زيارات الموقع', 'baseline' => 'يُحدد', 'target' => 'يُحدد', 'actual' => 'يُسجل شهرياً'],
                    ['name' => 'معدل التحويل', 'baseline' => 'يُحدد', 'target' => 'يُحدد', 'actual' => 'يُسجل شهرياً'],
                    ['name' => 'تكلفة اكتساب العميل', 'baseline' => 'يُحدد', 'target' => 'يُحدد', 'actual' => 'يُسجل شهرياً'],
                    ['name' => 'العائد على الإنفاق الإعلاني', 'baseline' => 'يُحدد', 'target' => 'يُحدد', 'actual' => 'يُسجل شهرياً'],
                    ['name' => 'معدل الاحتفاظ بالعملاء', 'baseline' => 'يُحدد', 'target' => 'يُحدد', 'actual' => 'يُسجل شهرياً'],
                ],
            ],
        ];

        // القسم 2: أداء القنوات
        $sections[] = [
            'title' => 'أداء القنوات التسويقية',
            'content' => [
                'channels' => [
                    ['name' => 'الموقع الإلكتروني', 'metrics' => ['الزيارات', 'معدل الارتداد', 'الصفحات لكل جلسة', 'مدة الجلسة']],
                    ['name' => 'وسائل التواصل الاجتماعي', 'metrics' => ['المتابعون', 'معدل التفاعل', 'الوصول', 'النقرات']],
                    ['name' => 'البريد الإلكتروني', 'metrics' => ['معدل الفتح', 'معدل النقر', 'إلغاء الاشتراك', 'التحويلات']],
                    ['name' => 'الإعلانات المدفوعة', 'metrics' => ['الإنفاق', 'النقرات', 'التحويلات', 'تكلفة التحويل']],
                ],
            ],
        ];

        // القسم 3: الميزانية
        $sections[] = [
            'title' => 'متابعة الميزانية',
            'content' => [
                'budget_template' => [
                    ['category' => 'الإعلانات الرقمية', 'planned' => 'يُحدد', 'actual' => 'يُسجل', 'variance' => 'يُحسب'],
                    ['category' => 'المحتوى والإبداع', 'planned' => 'يُحدد', 'actual' => 'يُسجل', 'variance' => 'يُحسب'],
                    ['category' => 'الأدوات والتقنيات', 'planned' => 'يُحدد', 'actual' => 'يُسجل', 'variance' => 'يُحسب'],
                    ['category' => 'الفعاليات والعلاقات', 'planned' => 'يُحدد', 'actual' => 'يُسجل', 'variance' => 'يُحسب'],
                ],
            ],
        ];

        // القسم 4: الإنجازات والتحديات
        $sections[] = [
            'title' => 'الإنجازات والتحديات',
            'content' => [
                'achievements_template' => 'قائمة الإنجازات المحققة هذا الشهر - يُعبأ شهرياً',
                'challenges_template'   => 'قائمة التحديات والعقبات - يُعبأ شهرياً',
                'lessons_learned'       => 'الدروس المستفادة - يُعبأ شهرياً',
                'next_month_priorities' => 'أولويات الشهر القادم - يُعبأ شهرياً',
            ],
        ];

        return $this->buildReport('قالب تقرير الأداء الشهري', 'monthly_performance', $sections, [
            'page_count' => 4,
            'audience'   => 'فريق التسويق',
            'frequency'  => 'شهري',
        ]);
    }

    /**
     * تقرير الاستخبارات التنافسية - تحليل الموقع السوقي
     * @param int $sessionId معرف الجلسة
     * @return array تقرير الاستخبارات التنافسية
     */
    public function generateCompetitiveIntelligence(int $sessionId): array {
        $data = $this->loadSessionData($sessionId);
        $scores = $data['scores'];
        $answers = $data['answers'];
        $company = $data['company'];
        $sections = [];

        $sector = $company['sector'] ?? 'general';
        $answersMap = $this->mapAnswers($answers);

        // القسم 1: الموقع التنافسي الحالي
        $competitionLevel = $answersMap['competition_level'] ?? 'medium';
        $marketShare = (float)($answersMap['market_share'] ?? 0);
        $sections[] = [
            'title' => 'الموقع التنافسي الحالي',
            'content' => [
                'sector'            => SECTORS[$sector]['ar'] ?? $sector,
                'competition_level' => $this->getCompetitionLabel($competitionLevel),
                'market_share'      => $marketShare > 0 ? $marketShare . '%' : 'غير محدد',
                'competitive_score' => (int)($scores['overall'] ?? 50),
                'position_analysis' => $this->buildPositionAnalysis($scores, $competitionLevel),
            ],
        ];

        // القسم 2: نقاط القوة والضعف التنافسية
        $sections[] = [
            'title' => 'نقاط القوة والضعف التنافسية',
            'content' => [
                'strengths'  => $this->extractCompetitiveStrengths($scores, $answersMap),
                'weaknesses' => $this->extractCompetitiveWeaknesses($scores, $answersMap),
            ],
        ];

        // القسم 3: تحليل القطاع
        $sections[] = [
            'title' => 'تحليل اتجاهات القطاع',
            'content' => [
                'market_trend'     => $this->getMarketTrendLabel($answersMap['market_trend'] ?? 'stable'),
                'digital_adoption' => $this->getSectorDigitalAdoption($sector),
                'key_success_factors' => $this->getSectorSuccessFactors($sector),
            ],
        ];

        // القسم 4: الفرص التنافسية
        $sections[] = [
            'title' => 'الفرص التنافسية المتاحة',
            'content' => [
                'opportunities' => $this->identifyCompetitiveOpportunities($scores, $answersMap, $sector),
                'threats'       => $this->identifyCompetitiveThreats($scores, $answersMap),
            ],
        ];

        // القسم 5: التوصيات التنافسية
        $sections[] = [
            'title' => 'التوصيات لتعزيز الموقع التنافسي',
            'content' => [
                'recommendations' => $this->buildCompetitiveRecommendations($scores, $competitionLevel),
            ],
        ];

        return $this->buildReport('تقرير الاستخبارات التنافسية', 'competitive_intelligence', $sections, [
            'page_count' => 5,
            'audience'   => 'الإدارة والتخطيط الاستراتيجي',
        ]);
    }

    /**
     * حفظ التقرير في جدول reports
     * @param int $sessionId معرف الجلسة
     * @param string $type نوع التقرير
     * @param array $content محتوى التقرير
     * @return int معرف التقرير
     */
    public function save(int $sessionId, string $type, array $content): int {
        return $this->db->insert('reports', [
            'session_id'   => $sessionId,
            'report_type'  => $type,
            'title'        => $content['title'] ?? self::REPORT_TYPES[$type] ?? $type,
            'content'      => json_encode($content, JSON_UNESCAPED_UNICODE),
            'generated_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * جلب تقرير بمعرفه
     * @param int $reportId معرف التقرير
     * @return array|null التقرير أو null
     */
    public function getReport(int $reportId): ?array {
        $row = $this->db->fetch(
            "SELECT * FROM reports WHERE id = :id",
            ['id' => $reportId]
        );

        if (!$row) {
            return null;
        }

        $row['content'] = json_decode($row['content'] ?? '{}', true) ?: [];
        return $row;
    }

    /**
     * جلب جميع تقارير الجلسة
     * @param int $sessionId معرف الجلسة
     * @return array قائمة التقارير
     */
    public function getSessionReports(int $sessionId): array {
        $rows = $this->db->fetchAll(
            "SELECT id, report_type, title, generated_at FROM reports WHERE session_id = :sid ORDER BY generated_at DESC",
            ['sid' => $sessionId]
        );

        return $rows;
    }

    // ==================== الدوال المساعدة الخاصة ====================

    private function loadSessionData(int $sessionId): array {
        $session = $this->db->fetch(
            "SELECT s.*, c.name as company_name, c.sector, c.years_in_business,
                    c.employee_count, c.annual_revenue
             FROM assessment_sessions s
             JOIN companies c ON s.company_id = c.id
             WHERE s.id = :id",
            ['id' => $sessionId]
        );

        $analysisResult = $this->db->fetch(
            "SELECT * FROM analysis_results WHERE session_id = :sid",
            ['sid' => $sessionId]
        );

        $answers = $this->db->fetchAll(
            "SELECT question_id, answer_value, field_mapping FROM answers WHERE session_id = :sid",
            ['sid' => $sessionId]
        );

        return [
            'session' => $session ?: [],
            'company' => [
                'name'              => $session['company_name'] ?? '',
                'sector'            => $session['sector'] ?? '',
                'years_in_business' => $session['years_in_business'] ?? 0,
                'employee_count'    => $session['employee_count'] ?? 0,
                'annual_revenue'    => $session['annual_revenue'] ?? 0,
            ],
            'scores'  => json_decode($analysisResult['scores'] ?? '{}', true) ?: [],
            'answers' => $answers,
        ];
    }

    private function loadRecommendations(int $sessionId): array {
        $result = $this->db->fetch(
            "SELECT recommendations FROM analysis_results WHERE session_id = :sid",
            ['sid' => $sessionId]
        );
        return json_decode($result['recommendations'] ?? '[]', true) ?: [];
    }

    private function loadAlerts(int $sessionId): array {
        $result = $this->db->fetch(
            "SELECT alerts FROM analysis_results WHERE session_id = :sid",
            ['sid' => $sessionId]
        );
        return json_decode($result['alerts'] ?? '[]', true) ?: [];
    }

    private function buildReport(string $title, string $type, array $sections, array $metadata): array {
        return [
            'title'        => $title,
            'type'         => $type,
            'sections'     => $sections,
            'generated_at' => date('Y-m-d H:i:s'),
            'metadata'     => $metadata,
        ];
    }

    private function buildOverallSummaryText(array $company, int $overall): string {
        $name = $company['name'] ?: 'الشركة';
        $sector = SECTORS[$company['sector'] ?? '']['ar'] ?? ($company['sector'] ?? 'عام');
        $level = $this->getScoreLevelLabel($overall);

        return sprintf(
            'بناءً على التقييم الشامل لأعمال %s في قطاع %s، بلغت الدرجة الإجمالية %d/100 (%s). ',
            $name, $sector, $overall, $level
        ) . $this->getOverallVerdict($overall);
    }

    private function getOverallVerdict(int $overall): string {
        if ($overall < 25) {
            return 'الوضع الحالي حرج ويتطلب تدخلاً عاجلاً لمعالجة المشكلات الأساسية.';
        }
        if ($overall < 50) {
            return 'هناك نقاط ضعف جوهرية تحتاج معالجة منهجية لتحقيق الاستقرار.';
        }
        if ($overall < 75) {
            return 'الأساس جيد مع فرص واضحة للتحسين والنمو.';
        }
        return 'الأداء التسويقي متقدم مع إمكانية التحول إلى قيادة السوق.';
    }

    private function extractStrengths(array $scores): array {
        $strengths = [];
        foreach (self::ANALYSIS_DIMENSIONS as $key => $label) {
            $score = (int)($scores[$key] ?? 50);
            if ($score >= 60) {
                $strengths[] = ['dimension' => $label, 'score' => $score, 'level' => $this->getScoreLevelLabel($score)];
            }
        }
        return $strengths;
    }

    private function extractWeaknesses(array $scores): array {
        $weaknesses = [];
        foreach (self::ANALYSIS_DIMENSIONS as $key => $label) {
            $score = (int)($scores[$key] ?? 50);
            if ($score < 40) {
                $weaknesses[] = ['dimension' => $label, 'score' => $score, 'level' => $this->getScoreLevelLabel($score)];
            }
        }
        return $weaknesses;
    }

    private function buildKeyMetrics(array $scores): array {
        $metrics = [];
        foreach (self::ANALYSIS_DIMENSIONS as $key => $label) {
            $score = (int)($scores[$key] ?? 50);
            $metrics[] = ['name' => $label, 'value' => $score, 'max' => 100, 'level' => $this->getScoreLevelLabel($score)];
        }
        return $metrics;
    }

    private function buildNextSteps(int $overall): array {
        if ($overall < 30) {
            return ['تفعيل خطة الطوارئ فوراً', 'تحديد المشكلات الحرجة ومعالجتها', 'مراجعة أسبوعية للتقدم'];
        }
        if ($overall < 60) {
            return ['وضع خطة علاجية للأبعاد الضعيفة', 'تحسين القنوات الحالية', 'بناء نظام قياس فعّال'];
        }
        return ['توسيع القنوات الناجحة', 'استكشاف فرص جديدة', 'الاستثمار في الابتكار'];
    }

    private function buildDimensionSummary(array $scores): array {
        $summary = [];
        foreach (self::ANALYSIS_DIMENSIONS as $key => $label) {
            $score = (int)($scores[$key] ?? 50);
            $summary[$key] = ['label' => $label, 'score' => $score, 'level' => $this->getScoreLevelLabel($score)];
        }
        return $summary;
    }

    private function buildDimensionAnalysis(string $dimension, int $score, array $details): string {
        $label = self::ANALYSIS_DIMENSIONS[$dimension] ?? $dimension;
        $level = $this->getScoreLevelLabel($score);
        return sprintf('بُعد %s يسجل %d/100 (مستوى: %s). ', $label, $score, $level)
             . ($score < 40 ? 'يحتاج هذا البعد إلى اهتمام عاجل وتحسينات جوهرية.' : ($score < 70 ? 'يوجد مجال للتحسين مع أساس يمكن البناء عليه.' : 'أداء جيد يمكن تعزيزه والبناء عليه لتحقيق التميز.'));
    }

    private function buildDimensionRecommendations(string $dimension, int $score): array {
        if ($score >= 70) {
            return ['الحفاظ على المستوى الحالي وتعزيزه', 'مشاركة أفضل الممارسات مع الأبعاد الأخرى'];
        }
        if ($score >= 40) {
            return ['وضع خطة تحسين مرحلية', 'تخصيص موارد إضافية', 'قياس التقدم شهرياً'];
        }
        return ['تدخل عاجل لمعالجة الفجوات', 'استشارة متخصصين في المجال', 'وضع أهداف قصيرة المدى قابلة للقياس'];
    }

    private function identifyGaps(array $scores): array {
        $gaps = [];
        foreach (self::ANALYSIS_DIMENSIONS as $key => $label) {
            $current = (int)($scores[$key] ?? 50);
            $target = 70;
            if ($current < $target) {
                $gaps[] = ['dimension' => $label, 'current' => $current, 'target' => $target, 'gap' => $target - $current];
            }
        }
        usort($gaps, fn($a, $b) => $b['gap'] <=> $a['gap']);
        return $gaps;
    }

    private function determinePlanType(int $overall, int $riskScore): string {
        if ($riskScore > 70 || $overall < 20) {
            return 'emergency';
        }
        if ($overall < 40) {
            return 'treatment';
        }
        if ($overall < 70) {
            return 'growth';
        }
        return 'transformation';
    }

    private function getPlanObjective(string $planType): string {
        return match ($planType) {
            'emergency'      => 'وقف التراجع واستقرار الوضع التسويقي خلال 30 يوماً',
            'treatment'      => 'معالجة نقاط الضعف الأساسية وبناء قاعدة صلبة خلال 3 أشهر',
            'growth'         => 'تسريع النمو وزيادة الحصة السوقية خلال 6 أشهر',
            'transformation' => 'التحول النوعي والريادة في السوق خلال 12 شهراً',
            default          => 'تحسين الأداء التسويقي العام',
        };
    }

    private function buildPhaseKPIs(string $phase): array {
        return match ($phase) {
            'immediate' => [
                ['name' => 'إيقاف مصادر الهدر', 'target' => 'خلال أسبوع'],
                ['name' => 'تركيب أدوات التتبع', 'target' => 'خلال أسبوعين'],
                ['name' => 'تحديد المؤشرات الأساسية', 'target' => 'خلال أسبوع'],
            ],
            'phase_one' => [
                ['name' => 'زيادة الوعي بالعلامة', 'target' => '15% تحسن'],
                ['name' => 'تحسين معدل التحويل', 'target' => '10% تحسن'],
                ['name' => 'خفض تكلفة الاكتساب', 'target' => '20% انخفاض'],
            ],
            'phase_two' => [
                ['name' => 'نمو الإيرادات من التسويق', 'target' => '20% زيادة'],
                ['name' => 'تحسين العائد على الاستثمار', 'target' => '30% تحسن'],
                ['name' => 'زيادة الحصة السوقية', 'target' => '5% زيادة'],
            ],
            default => [],
        };
    }

    private function buildSuccessCriteria(int $overall): array {
        $targetScore = min(100, $overall + 20);
        return [
            'الوصول بالدرجة الإجمالية إلى ' . $targetScore . '/100',
            'تحقيق عائد إيجابي على الاستثمار التسويقي',
            'تحسين جميع الأبعاد الأقل من 40 درجة',
            'بناء نظام قياس ومتابعة فعّال',
        ];
    }

    private function getCompetitionLabel(string $level): string {
        return match ($level) {
            'very_high' => 'شديدة جداً',
            'high'      => 'شديدة',
            'medium'    => 'متوسطة',
            'low'       => 'منخفضة',
            default     => $level,
        };
    }

    private function getMarketTrendLabel(string $trend): string {
        return match ($trend) {
            'growing'           => 'سوق في نمو - فرصة للتوسع',
            'stable'            => 'سوق مستقر - التركيز على الحصة السوقية',
            'declining'         => 'سوق في تراجع - التنويع مطلوب',
            'sharply_declining'  => 'سوق في انحدار حاد - تحول استراتيجي ضروري',
            default             => 'غير محدد',
        };
    }

    private function buildPositionAnalysis(array $scores, string $competitionLevel): string {
        $overall = (int)($scores['overall'] ?? 50);
        if ($overall >= 70 && in_array($competitionLevel, ['low', 'medium'], true)) {
            return 'موقع تنافسي قوي مع فرصة للهيمنة على السوق';
        }
        if ($overall >= 50) {
            return 'موقع تنافسي متوسط مع إمكانية التحسن';
        }
        return 'موقع تنافسي ضعيف يحتاج تعزيزاً عاجلاً';
    }

    private function extractCompetitiveStrengths(array $scores, array $answers): array {
        $strengths = [];
        if ((int)($scores['digital_maturity'] ?? 0) >= 60) {
            $strengths[] = 'حضور رقمي قوي مقارنة بالمنافسين';
        }
        if ((float)($answers['customer_satisfaction'] ?? 0) > 7) {
            $strengths[] = 'مستوى رضا عملاء عالٍ يمثل ميزة تنافسية';
        }
        if ((float)($answers['product_quality'] ?? 0) > 7) {
            $strengths[] = 'جودة منتجات/خدمات متميزة';
        }
        if ((float)($answers['brand_strength'] ?? 0) > 6) {
            $strengths[] = 'علامة تجارية معروفة وموثوقة';
        }
        if (empty($strengths)) {
            $strengths[] = 'يجب العمل على بناء مزايا تنافسية واضحة';
        }
        return $strengths;
    }

    private function extractCompetitiveWeaknesses(array $scores, array $answers): array {
        $weaknesses = [];
        if ((int)($scores['digital_maturity'] ?? 100) < 40) {
            $weaknesses[] = 'تأخر في التحول الرقمي مقارنة بالمنافسين';
        }
        if ((int)($scores['marketing_maturity'] ?? 100) < 40) {
            $weaknesses[] = 'ممارسات تسويقية أقل نضجاً من المنافسين';
        }
        if ((float)($answers['differentiation'] ?? 10) < 4) {
            $weaknesses[] = 'ضعف التميز والتفرد في السوق';
        }
        if ((float)($answers['brand_awareness'] ?? 10) < 4) {
            $weaknesses[] = 'انخفاض الوعي بالعلامة التجارية';
        }
        return $weaknesses;
    }

    private function getSectorDigitalAdoption(string $sector): string {
        $highDigital = ['retail', 'fnb', 'fitness', 'education'];
        if (in_array($sector, $highDigital, true)) {
            return 'قطاع ذو اعتماد رقمي عالٍ - التواجد الرقمي ضرورة وليس خياراً';
        }
        return 'قطاع ذو اعتماد رقمي متوسط - التحول الرقمي يمنح ميزة تنافسية';
    }

    private function getSectorSuccessFactors(string $sector): array {
        $common = ['جودة الخدمة والمنتج', 'تجربة العميل المتميزة', 'التسويق الرقمي الفعّال'];
        $specific = match ($sector) {
            'education'             => ['محتوى تعليمي متميز', 'سمعة أكاديمية قوية'],
            'healthcare'            => ['الثقة والمصداقية', 'التقنيات الحديثة'],
            'fnb'                   => ['التواجد على منصات التوصيل', 'التقييمات الإيجابية'],
            'retail'                => ['تجربة تسوق متكاملة', 'التسعير التنافسي'],
            'professional_services' => ['الخبرة والتخصص', 'شبكة العلاقات'],
            'real_estate'           => ['الموقع والسمعة', 'المحتوى المرئي الاحترافي'],
            'fitness'               => ['المجتمع والتفاعل', 'النتائج الملموسة'],
            'crafts'                => ['القصة والأصالة', 'التواجد على منصات البيع'],
            default                 => ['التميز في الخدمة'],
        };
        return array_merge($common, $specific);
    }

    private function identifyCompetitiveOpportunities(array $scores, array $answers, string $sector): array {
        $opportunities = [];
        if ((int)($scores['digital_maturity'] ?? 50) < 50) {
            $opportunities[] = 'التحول الرقمي السريع للتفوق على المنافسين التقليديين';
        }
        if ((float)($answers['market_gaps'] ?? 0) > 5) {
            $opportunities[] = 'ملء الفجوات السوقية التي لم يلبيها المنافسون';
        }
        if (($answers['market_trend'] ?? '') === 'growing') {
            $opportunities[] = 'اقتناص فرص السوق النامي قبل تشبعه';
        }
        $opportunities[] = 'بناء تجربة عملاء متفوقة كميزة تنافسية مستدامة';
        return $opportunities;
    }

    private function identifyCompetitiveThreats(array $scores, array $answers): array {
        $threats = [];
        $competition = $answers['competition_level'] ?? '';
        if (in_array($competition, ['high', 'very_high'], true)) {
            $threats[] = 'منافسة شديدة قد تضغط على الأسعار والحصة السوقية';
        }
        if (($answers['market_trend'] ?? '') === 'declining') {
            $threats[] = 'تراجع السوق يهدد جميع اللاعبين';
        }
        if ((int)($scores['risk_score'] ?? 0) > 50) {
            $threats[] = 'مستوى مخاطر مرتفع يتطلب حماية استباقية';
        }
        $threats[] = 'دخول منافسين جدد بنماذج عمل مبتكرة';
        return $threats;
    }

    private function buildCompetitiveRecommendations(array $scores, string $competitionLevel): array {
        $recs = ['تحديد وتعزيز عوامل التميز الفريدة'];
        if (in_array($competitionLevel, ['high', 'very_high'], true)) {
            $recs[] = 'التركيز على شريحة سوقية محددة بدلاً من التنافس الشامل';
            $recs[] = 'بناء ولاء العملاء كحاجز ضد المنافسة';
        }
        if ((int)($scores['digital_maturity'] ?? 50) < 50) {
            $recs[] = 'تسريع التحول الرقمي للحاق بالمنافسين أو تجاوزهم';
        }
        $recs[] = 'مراقبة المنافسين بشكل دوري وتحديث الاستراتيجية وفقاً لذلك';
        return $recs;
    }

    private function getScoreLevel(int $score): string {
        if ($score >= 76) {
            return 'expert';
        }
        if ($score >= 51) {
            return 'advanced';
        }
        if ($score >= 26) {
            return 'developing';
        }
        return 'beginner';
    }

    private function getScoreLevelLabel(int $score): string {
        if ($score >= 76) {
            return 'متقدم جداً';
        }
        if ($score >= 51) {
            return 'جيد';
        }
        if ($score >= 26) {
            return 'نامي';
        }
        return 'مبتدئ';
    }

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
