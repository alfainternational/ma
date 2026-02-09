<?php
/**
 * RecommendationEngine - محرك التوصيات الثلاثي المستويات
 * يولّد توصيات استراتيجية وتكتيكية وتنفيذية بناءً على نتائج التحليل
 * Marketing AI System
 */
class RecommendationEngine {
    private Database $db;

    /** ثوابت مصفوفة التأثير/الجهد */
    private const IMPACT_WEIGHTS = [
        'critical' => 4,
        'high'     => 3,
        'medium'   => 2,
        'low'      => 1,
    ];

    private const EFFORT_LEVELS = [
        'minimal'    => 1,
        'low'        => 2,
        'medium'     => 3,
        'high'       => 4,
        'very_high'  => 5,
    ];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * توليد جميع التوصيات عبر الطبقات الثلاث
     * @param int $sessionId معرف الجلسة
     * @param array $scores نتائج التقييم
     * @param array $expertResults نتائج تحليل الخبراء
     * @return array التوصيات الكاملة مرتبة حسب الأولوية
     */
    public function generateAll(int $sessionId, array $scores, array $expertResults): array {
        $context = $this->loadSessionContext($sessionId);

        $strategic = $this->generateStrategic($scores, $context);
        $tactical = $this->generateTactical($scores, $strategic);
        $execution = $this->generateExecution($tactical);

        $allRecommendations = array_merge($strategic, $tactical, $execution);
        $prioritized = $this->prioritize($allRecommendations);

        $this->save($sessionId, $prioritized);

        return [
            'strategic'  => $strategic,
            'tactical'   => $tactical,
            'execution'  => $execution,
            'prioritized' => $prioritized,
            'summary' => [
                'total'    => count($prioritized),
                'critical' => count(array_filter($prioritized, fn($r) => $r['priority'] === 'critical')),
                'high'     => count(array_filter($prioritized, fn($r) => $r['priority'] === 'high')),
                'medium'   => count(array_filter($prioritized, fn($r) => $r['priority'] === 'medium')),
                'low'      => count(array_filter($prioritized, fn($r) => $r['priority'] === 'low')),
            ],
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * توليد التوصيات الاستراتيجية - التوجه العام والرؤية
     * @param array $scores نتائج التقييم
     * @param array $context سياق الجلسة
     * @return array 3-5 توصيات استراتيجية
     */
    public function generateStrategic(array $scores, array $context): array {
        $overall = (int)($scores['overall'] ?? 50);
        $riskScore = (int)($scores['risk_score'] ?? 50);
        $opportunityScore = (int)($scores['opportunity_score'] ?? 50);
        $digitalMaturity = (int)($scores['digital_maturity'] ?? 50);
        $marketingMaturity = (int)($scores['marketing_maturity'] ?? 50);
        $recommendations = [];

        // تحديد نوع الخطة
        $planType = $this->determinePlanType($overall, $riskScore);

        // توصية 1: التوجه الاستراتيجي الرئيسي
        $recommendations[] = $this->buildRecommendation(
            $this->getStrategicDirectionTitle($planType),
            $this->getStrategicDirectionDescription($planType, $overall),
            $overall < 30 ? 'critical' : ($overall < 50 ? 'high' : 'medium'),
            'strategic',
            $this->getStrategicActions($planType),
            $this->getTimeHorizon($planType),
            $this->getEstimatedImpact($overall, 'strategic'),
            $overall < 40 ? 'high' : 'medium'
        );

        // توصية 2: مجالات التركيز
        $focusAreas = $this->identifyFocusAreas($scores);
        if (!empty($focusAreas)) {
            $recommendations[] = $this->buildRecommendation(
                'تركيز الموارد على المجالات ذات الأثر الأعلى',
                'يجب توجيه الجهود نحو: ' . implode('، ', array_column($focusAreas, 'label')),
                'high',
                'strategic',
                array_map(fn($area) => 'تحسين ' . $area['label'] . ' من ' . $area['current'] . ' إلى ' . $area['target'], $focusAreas),
                $this->getTimeHorizon($planType),
                'مرتفع - تحسين متوقع بنسبة 20-35%',
                'medium'
            );
        }

        // توصية 3: إدارة المخاطر
        if ($riskScore > 40) {
            $recommendations[] = $this->buildRecommendation(
                'خطة إدارة المخاطر والتخفيف',
                'مستوى المخاطر الحالي ' . $riskScore . '/100 ويتطلب إجراءات وقائية',
                $riskScore > 70 ? 'critical' : 'high',
                'strategic',
                [
                    'تنويع مصادر الإيرادات والقنوات التسويقية',
                    'بناء احتياطي مالي للطوارئ التسويقية',
                    'وضع خطة بديلة للسيناريوهات السلبية',
                ],
                '3 أشهر',
                'وقائي - حماية الأعمال من خسائر محتملة',
                'medium'
            );
        }

        // توصية 4: استغلال الفرص
        if ($opportunityScore > 50) {
            $recommendations[] = $this->buildRecommendation(
                'استراتيجية استغلال الفرص المتاحة',
                'يوجد فرص نمو واعدة بدرجة ' . $opportunityScore . '/100 يجب اقتناصها',
                'medium',
                'strategic',
                [
                    'تخصيص ميزانية لاستكشاف الفرص الجديدة',
                    'إطلاق مشاريع تجريبية في المجالات الواعدة',
                    'بناء شراكات استراتيجية لتسريع النمو',
                ],
                '6 أشهر',
                'مرتفع - إمكانية نمو 25-40%',
                'medium'
            );
        }

        // توصية 5: بناء القدرات
        if ($digitalMaturity < 40 || $marketingMaturity < 40) {
            $recommendations[] = $this->buildRecommendation(
                'برنامج بناء القدرات التسويقية',
                'مستوى النضج الحالي يتطلب استثماراً في بناء القدرات الأساسية',
                'high',
                'strategic',
                [
                    'تقييم الفجوات في المهارات والأدوات',
                    'وضع خطة تدريب وتطوير للفريق',
                    'اعتماد أدوات وتقنيات تسويقية حديثة',
                ],
                '6 أشهر',
                'أساسي - بنية تحتية للنمو المستقبلي',
                'high'
            );
        }

        return $recommendations;
    }

    /**
     * توليد التوصيات التكتيكية - قنوات وميزانيات وحملات
     * @param array $scores نتائج التقييم
     * @param array $strategicRecs التوصيات الاستراتيجية
     * @return array 5-10 توصيات تكتيكية
     */
    public function generateTactical(array $scores, array $strategicRecs): array {
        $digital = (int)($scores['digital_maturity'] ?? 50);
        $marketing = (int)($scores['marketing_maturity'] ?? 50);
        $organizational = (int)($scores['organizational_readiness'] ?? 50);
        $recommendations = [];

        // تكتيك 1: الموقع الإلكتروني
        if ($digital < 60) {
            $recommendations[] = $this->buildRecommendation(
                'تحسين الموقع الإلكتروني وتجربة المستخدم',
                'الموقع هو الواجهة الرقمية الأولى ويحتاج تطويراً',
                $digital < 30 ? 'critical' : 'high',
                'tactical',
                ['تحسين سرعة التحميل', 'تصميم متجاوب مع الجوال', 'تحسين محركات البحث SEO', 'إضافة تتبع التحويلات'],
                'شهر واحد',
                'زيادة الزيارات بنسبة 30-50%',
                'medium'
            );
        }

        // تكتيك 2: وسائل التواصل الاجتماعي
        $recommendations[] = $this->buildRecommendation(
            'استراتيجية وسائل التواصل الاجتماعي',
            'بناء حضور فعّال ومتسق على المنصات المناسبة',
            $digital < 40 ? 'high' : 'medium',
            'tactical',
            ['تحديد المنصات الأنسب للجمهور المستهدف', 'إنشاء تقويم محتوى شهري', 'تخصيص ميزانية إعلانات مدفوعة', 'قياس التفاعل والوصول أسبوعياً'],
            'شهرين',
            'زيادة الوصول والتفاعل بنسبة 40-60%',
            'low'
        );

        // تكتيك 3: التسويق بالمحتوى
        if ($marketing < 60) {
            $recommendations[] = $this->buildRecommendation(
                'خطة تسويق بالمحتوى',
                'المحتوى القيّم يبني الثقة ويجذب العملاء المحتملين',
                'medium',
                'tactical',
                ['تحديد مواضيع تهم الجمهور المستهدف', 'إنتاج مقالات ومنشورات أسبوعياً', 'إنشاء محتوى مرئي (فيديو/إنفوجرافيك)', 'توزيع المحتوى عبر القنوات المناسبة'],
                '3 أشهر',
                'بناء سلطة في المجال وزيادة الثقة',
                'medium'
            );
        }

        // تكتيك 4: الإعلانات الرقمية
        $recommendations[] = $this->buildRecommendation(
            'حملات إعلانية رقمية مستهدفة',
            'استثمار مدروس في الإعلانات لتحقيق نتائج سريعة',
            'high',
            'tactical',
            ['إطلاق حملات جوجل للكلمات المفتاحية الأساسية', 'حملات إعادة استهداف للزوار السابقين', 'اختبار A/B للإعلانات والصفحات', 'تحسين الحملات أسبوعياً بناءً على البيانات'],
            'شهر واحد',
            'عائد 3-5 أضعاف على الإنفاق الإعلاني',
            'low'
        );

        // تكتيك 5: البريد الإلكتروني
        $recommendations[] = $this->buildRecommendation(
            'برنامج تسويق عبر البريد الإلكتروني',
            'بناء قائمة بريدية وتفعيل التواصل المنتظم مع العملاء',
            'medium',
            'tactical',
            ['بناء قائمة بريدية مع عروض تحفيزية', 'إنشاء سلسلة ترحيب تلقائية', 'إرسال نشرة إخبارية شهرية', 'تقسيم القائمة حسب الاهتمامات'],
            'شهرين',
            'زيادة معدل التحويل بنسبة 15-25%',
            'low'
        );

        // تكتيك 6: تحسين تجربة العملاء
        $recommendations[] = $this->buildRecommendation(
            'برنامج تحسين تجربة العملاء',
            'تجربة العملاء المتميزة تزيد الولاء والإحالات',
            'medium',
            'tactical',
            ['رسم خريطة رحلة العميل الحالية', 'تحديد نقاط الألم والاحتكاك', 'تطبيق تحسينات فورية', 'إنشاء نظام ملاحظات مستمر'],
            '3 أشهر',
            'تحسين معدل الاحتفاظ بنسبة 10-20%',
            'medium'
        );

        // تكتيك 7: التحليلات وقياس الأداء
        if ($digital < 50 || $organizational < 50) {
            $recommendations[] = $this->buildRecommendation(
                'بناء نظام تحليلات وقياس أداء',
                'القرارات المبنية على البيانات تحقق نتائج أفضل',
                'high',
                'tactical',
                ['تركيب وتهيئة أدوات التحليل', 'تحديد مؤشرات الأداء الرئيسية', 'إنشاء لوحة متابعة أسبوعية', 'تدريب الفريق على قراءة البيانات'],
                'شهر واحد',
                'تحسين كفاءة الإنفاق بنسبة 20-30%',
                'low'
            );
        }

        return $recommendations;
    }

    /**
     * توليد خطة التنفيذ - إجراءات أسبوعية ومسؤوليات
     * @param array $tacticalRecs التوصيات التكتيكية
     * @return array 10-15 إجراء تنفيذي أسبوعي
     */
    public function generateExecution(array $tacticalRecs): array {
        $executions = [];
        $weekNumber = 1;

        foreach ($tacticalRecs as $tactical) {
            $actions = $tactical['actions'] ?? [];
            $priority = $tactical['priority'] ?? 'medium';

            foreach (array_slice($actions, 0, 2) as $action) {
                if ($weekNumber > 15) {
                    break 2;
                }

                $executions[] = $this->buildRecommendation(
                    $action,
                    'تنفيذ ضمن: ' . $tactical['title'],
                    $priority,
                    'execution',
                    [
                        'تحديد المسؤول عن التنفيذ',
                        'تخصيص الموارد اللازمة',
                        'تحديد معايير النجاح',
                        'مراجعة النتائج نهاية الأسبوع',
                    ],
                    'الأسبوع ' . $weekNumber,
                    $tactical['estimated_impact'] ?? 'حسب التكتيك المرتبط',
                    'low'
                );

                $weekNumber++;
            }
        }

        return $executions;
    }

    /**
     * ترتيب التوصيات حسب مصفوفة التأثير/الجهد
     * @param array $recommendations جميع التوصيات
     * @return array التوصيات مرتبة
     */
    public function prioritize(array $recommendations): array {
        usort($recommendations, function (array $a, array $b): int {
            $layerOrder = ['strategic' => 1, 'tactical' => 2, 'execution' => 3];
            $aImpact = self::IMPACT_WEIGHTS[$a['priority'] ?? 'low'] ?? 1;
            $bImpact = self::IMPACT_WEIGHTS[$b['priority'] ?? 'low'] ?? 1;
            $aEffort = self::EFFORT_LEVELS[$a['effort_level'] ?? 'medium'] ?? 3;
            $bEffort = self::EFFORT_LEVELS[$b['effort_level'] ?? 'medium'] ?? 3;

            // النتيجة = التأثير / الجهد (الأعلى أولاً)
            $aScore = $aImpact / max(1, $aEffort);
            $bScore = $bImpact / max(1, $bEffort);

            if ($aScore !== $bScore) {
                return $bScore <=> $aScore;
            }

            // عند التعادل، الاستراتيجي أولاً
            $aLayer = $layerOrder[$a['layer'] ?? 'execution'] ?? 3;
            $bLayer = $layerOrder[$b['layer'] ?? 'execution'] ?? 3;
            return $aLayer <=> $bLayer;
        });

        // إضافة ترتيب الأولوية
        foreach ($recommendations as $index => &$rec) {
            $rec['priority_rank'] = $index + 1;
        }

        return $recommendations;
    }

    /**
     * حفظ التوصيات في جدول analysis_results
     * @param int $sessionId معرف الجلسة
     * @param array $recommendations التوصيات
     */
    public function save(int $sessionId, array $recommendations): void {
        $existing = $this->db->fetch(
            "SELECT id FROM analysis_results WHERE session_id = :sid",
            ['sid' => $sessionId]
        );

        $data = json_encode($recommendations, JSON_UNESCAPED_UNICODE);

        if ($existing) {
            $this->db->update(
                'analysis_results',
                ['recommendations' => $data, 'updated_at' => date('Y-m-d H:i:s')],
                'session_id = :sid',
                ['sid' => $sessionId]
            );
        } else {
            $this->db->insert('analysis_results', [
                'session_id'      => $sessionId,
                'recommendations' => $data,
                'scores'          => json_encode([]),
                'insights'        => json_encode([]),
                'expert_analysis' => json_encode([]),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ==================== الدوال المساعدة الخاصة ====================

    private function loadSessionContext(int $sessionId): array {
        $session = $this->db->fetch(
            "SELECT s.context, c.sector, c.years_in_business, c.employee_count, c.annual_revenue
             FROM assessment_sessions s
             JOIN companies c ON s.company_id = c.id
             WHERE s.id = :id",
            ['id' => $sessionId]
        );

        if (!$session) {
            return [];
        }

        $context = json_decode($session['context'] ?? '{}', true) ?: [];
        $context['sector'] = $session['sector'];
        $context['years_in_business'] = $session['years_in_business'];
        $context['employee_count'] = $session['employee_count'];
        $context['annual_revenue'] = $session['annual_revenue'];

        return $context;
    }

    private function buildRecommendation(
        string $title,
        string $description,
        string $priority,
        string $layer,
        array $actions,
        string $timeline,
        string $estimatedImpact,
        string $effortLevel
    ): array {
        return [
            'title'            => $title,
            'description'      => $description,
            'priority'         => $priority,
            'layer'            => $layer,
            'actions'          => $actions,
            'timeline'         => $timeline,
            'estimated_impact' => $estimatedImpact,
            'effort_level'     => $effortLevel,
            'created_at'       => date('Y-m-d H:i:s'),
        ];
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

    private function getStrategicDirectionTitle(string $planType): string {
        return match ($planType) {
            'emergency'      => 'خطة طوارئ: تدخل فوري لإنقاذ الوضع التسويقي',
            'treatment'      => 'خطة علاجية: معالجة نقاط الضعف الأساسية',
            'growth'         => 'خطة نمو: تسريع التوسع وزيادة الحصة السوقية',
            'transformation' => 'خطة تحول: قيادة السوق والابتكار التسويقي',
            default          => 'توجه استراتيجي عام',
        };
    }

    private function getStrategicDirectionDescription(string $planType, int $overall): string {
        return match ($planType) {
            'emergency'      => 'الدرجة الكلية ' . $overall . '/100 تشير إلى وضع حرج يتطلب إجراءات عاجلة للحفاظ على استمرارية الأعمال وإيقاف التراجع فوراً',
            'treatment'      => 'الدرجة الكلية ' . $overall . '/100 تكشف عن نقاط ضعف جوهرية تحتاج معالجة منهجية قبل التفكير في النمو',
            'growth'         => 'الدرجة الكلية ' . $overall . '/100 تدل على أساس جيد يمكن البناء عليه لتحقيق نمو مُسرّع',
            'transformation' => 'الدرجة الكلية ' . $overall . '/100 تؤكد جاهزية الأعمال للانتقال إلى مرحلة القيادة والريادة',
            default          => 'تحليل الوضع الحالي بدرجة ' . $overall . '/100',
        };
    }

    private function getStrategicActions(string $planType): array {
        return match ($planType) {
            'emergency' => [
                'إيقاف جميع الأنشطة التسويقية غير الضرورية فوراً',
                'تركيز الموارد على القنوات ذات العائد المباشر',
                'وضع مؤشرات أداء يومية للمتابعة العاجلة',
                'مراجعة شاملة لهيكل التكاليف',
            ],
            'treatment' => [
                'تشخيص نقاط الضعف الأساسية بدقة',
                'وضع خطة علاجية لكل نقطة ضعف',
                'تحسين العمليات الحالية قبل التوسع',
                'بناء أنظمة قياس وتتبع فعّالة',
            ],
            'growth' => [
                'زيادة الاستثمار التسويقي في القنوات الناجحة',
                'استكشاف أسواق وشرائح عملاء جديدة',
                'تطوير شراكات استراتيجية للنمو',
                'الاستثمار في التميز والابتكار',
            ],
            'transformation' => [
                'إعادة تصميم نموذج العمل التسويقي',
                'استثمار كبير في التقنية والأتمتة',
                'بناء علامة تجارية رائدة في القطاع',
                'التوسع الجغرافي أو في فئات جديدة',
            ],
            default => ['وضع خطة تسويقية شاملة'],
        };
    }

    private function getTimeHorizon(string $planType): string {
        return match ($planType) {
            'emergency'      => '30 يوماً',
            'treatment'      => '3 أشهر',
            'growth'         => '6 أشهر',
            'transformation' => '12 شهراً',
            default          => '6 أشهر',
        };
    }

    private function getEstimatedImpact(int $overall, string $layer): string {
        if ($overall < 30) {
            return 'حرج - منع خسائر إضافية واستقرار الوضع';
        }
        if ($overall < 50) {
            return 'مرتفع - تحسين متوقع بنسبة 25-40% خلال 3 أشهر';
        }
        if ($overall < 70) {
            return 'متوسط إلى مرتفع - نمو متوقع بنسبة 15-30%';
        }
        return 'تحولي - قفزة نوعية في الأداء التسويقي';
    }

    private function identifyFocusAreas(array $scores): array {
        $dimensions = [
            ['key' => 'digital_maturity', 'label' => 'النضج الرقمي'],
            ['key' => 'marketing_maturity', 'label' => 'النضج التسويقي'],
            ['key' => 'organizational_readiness', 'label' => 'الجاهزية المؤسسية'],
        ];

        $areas = [];
        foreach ($dimensions as $dim) {
            $current = (int)($scores[$dim['key']] ?? 50);
            if ($current < 60) {
                $areas[] = [
                    'key'     => $dim['key'],
                    'label'   => $dim['label'],
                    'current' => $current,
                    'target'  => min(100, $current + 25),
                ];
            }
        }

        // ترتيب من الأضعف
        usort($areas, fn($a, $b) => $a['current'] <=> $b['current']);

        return array_slice($areas, 0, 3);
    }
}
