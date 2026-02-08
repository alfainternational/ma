<?php
/**
 * OperationsExpert - Execution & Operations Expert
 *
 * Evaluates team readiness, process efficiency, resource utilization,
 * and execution capabilities. Analyzes team skills, tool adoption,
 * and operational workflows to optimize marketing execution.
 */
class OperationsExpert extends ExpertBase {

    private const EFFICIENCY_LEVELS = [
        'optimized'  => ['label' => 'عمليات محسّنة', 'score' => 95],
        'streamlined'=> ['label' => 'عمليات منظمة', 'score' => 75],
        'functional' => ['label' => 'عمليات وظيفية', 'score' => 55],
        'developing' => ['label' => 'عمليات قيد التطوير', 'score' => 35],
        'chaotic'    => ['label' => 'عمليات عشوائية', 'score' => 15],
    ];

    private const TEAM_SIZE_BENCHMARKS = [
        'large'  => ['min' => 20, 'label' => 'فريق كبير'],
        'medium' => ['min' => 8,  'label' => 'فريق متوسط'],
        'small'  => ['min' => 3,  'label' => 'فريق صغير'],
        'micro'  => ['min' => 1,  'label' => 'فريق مصغّر'],
        'solo'   => ['min' => 0,  'label' => 'عمل فردي'],
    ];

    private const DIMENSION_WEIGHTS = [
        'team_size'           => 0.15,
        'team_skills'         => 0.25,
        'process_efficiency'  => 0.25,
        'tool_usage'          => 0.15,
        'execution_speed'     => 0.20,
    ];

    protected function initialize(): void {
        $this->id = 'operations_expert';
        $this->name = 'خبير التنفيذ والعمليات';
        $this->role = 'تقييم جاهزية الفريق وكفاءة العمليات واستخدام الموارد وسرعة التنفيذ';
        $this->expertiseAreas = [
            'operations_management',
            'team_development',
            'process_optimization',
            'resource_planning',
            'execution_excellence',
        ];
        $this->personality = [
            'organized'       => 0.95,
            'pragmatic'       => 0.9,
            'results_focused' => 0.85,
            'systematic'      => 0.9,
            'efficiency_driven'=> 0.9,
        ];
        $this->decisionWeight = 0.7;
    }

    public function analyze(array $answers, array $context, array $scores): array {
        $sector = $context['sector'] ?? 'general';
        $opsData = $this->extractOperationsData($answers, $context);

        $teamAssessment = $this->assessTeam($opsData);
        $processEfficiency = $this->evaluateProcesses($opsData);
        $resourceUtilization = $this->assessResources($opsData, $sector);

        $sections = [
            'team_assessment'      => $teamAssessment,
            'process_efficiency'   => $processEfficiency,
            'resource_utilization' => $resourceUtilization,
        ];

        $confidence = $this->calculateConfidence($opsData);

        $result = $this->buildResult(
            $sections,
            [
                'operational_readiness' => round($this->calculateOverallReadiness($teamAssessment, $processEfficiency, $resourceUtilization), 1),
                'team_capability'       => round($teamAssessment['capability_score'], 1),
                'execution_score'       => round($processEfficiency['execution_score'], 1),
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

        $readiness = $scores['operational_readiness'] ?? 50;
        $insights[] = $this->formatInsight(
            'الجاهزية التشغيلية',
            sprintf('مستوى الجاهزية التشغيلية: %s (%.0f/100)', $this->getScoreLabel($readiness), $readiness),
            $readiness >= 60 ? 'positive' : ($readiness >= 40 ? 'neutral' : 'negative'),
            0.87
        );

        $teamCapability = $scores['team_capability'] ?? 50;
        if ($teamCapability < 40) {
            $insights[] = $this->formatInsight(
                'فجوة في قدرات الفريق',
                'مهارات الفريق الحالية لا تغطي الاحتياجات التسويقية الأساسية وتحتاج تطويراً أو تعزيزاً',
                'warning',
                0.85
            );
        } elseif ($teamCapability >= 75) {
            $insights[] = $this->formatInsight(
                'فريق ذو كفاءة عالية',
                'الفريق يمتلك مهارات قوية تمكّنه من تنفيذ استراتيجيات تسويقية متقدمة',
                'positive',
                0.85
            );
        }

        $executionScore = $scores['execution_score'] ?? 50;
        if ($executionScore < 40) {
            $insights[] = $this->formatInsight(
                'بطء في التنفيذ',
                'سرعة وكفاءة التنفيذ أقل من المطلوب مما يعيق تحقيق الأهداف التسويقية',
                'negative',
                0.83
            );
        }

        $toolScore = $sections['resource_utilization']['tool_score'] ?? 50;
        if ($toolScore < 35) {
            $insights[] = $this->formatInsight(
                'نقص في الأدوات التسويقية',
                'الأدوات المستخدمة حالياً غير كافية لدعم العمليات التسويقية بكفاءة',
                'warning',
                0.80
            );
        }

        return $insights;
    }

    public function generateRecommendations(array $analysisResult): array {
        $recommendations = [];
        $scores = $analysisResult['scores'] ?? [];

        $teamCapability = $scores['team_capability'] ?? 50;
        if ($teamCapability < 40) {
            $recommendations[] = $this->formatRecommendation(
                'تطوير قدرات الفريق',
                'الفريق يحتاج تطويراً عاجلاً في المهارات الأساسية لتحقيق الأهداف التسويقية',
                'critical',
                [
                    'تحديد الفجوات في المهارات وترتيبها حسب الأولوية',
                    'وضع خطة تدريب مكثفة للمهارات الأكثر حاجة',
                    'الاستعانة بمتخصصين خارجيين لسد الفجوات الفورية',
                    'تقييم الحاجة لتوظيف كفاءات جديدة في المجالات الناقصة',
                ]
            );
        } elseif ($teamCapability < 65) {
            $recommendations[] = $this->formatRecommendation(
                'رفع كفاءة الفريق',
                'هناك فرصة لتحسين أداء الفريق من خلال التدريب والتطوير المستمر',
                'high',
                [
                    'تنظيم ورش عمل تخصصية شهرية',
                    'تشجيع الحصول على شهادات مهنية في التسويق الرقمي',
                    'بناء ثقافة التعلم المستمر ومشاركة المعرفة',
                ]
            );
        }

        $executionScore = $scores['execution_score'] ?? 50;
        if ($executionScore < 50) {
            $recommendations[] = $this->formatRecommendation(
                'تحسين عمليات التنفيذ',
                'العمليات الحالية تحتاج هيكلة وتنظيماً لتسريع التنفيذ وتقليل الهدر',
                'high',
                [
                    'توثيق العمليات التسويقية الأساسية وتوحيدها',
                    'تطبيق منهجية إدارة مشاريع مناسبة (Agile أو ما يناسب)',
                    'أتمتة المهام المتكررة لتحرير وقت الفريق',
                    'وضع جداول زمنية واضحة مع نقاط مراجعة دورية',
                ]
            );
        }

        $readiness = $scores['operational_readiness'] ?? 50;
        if ($readiness >= 70) {
            $recommendations[] = $this->formatRecommendation(
                'توسيع القدرات التشغيلية',
                'العمليات في حالة جيدة ويمكن التوسع لدعم أهداف نمو أكبر',
                'medium',
                [
                    'الاستثمار في أدوات أتمتة تسويقية متقدمة',
                    'بناء فريق متعدد التخصصات لمشاريع النمو',
                    'تطوير مؤشرات أداء متقدمة للعمليات',
                ]
            );
        }

        return $recommendations;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function extractOperationsData(array $answers, array $context): array {
        return [
            'team_size'          => (int) $this->extractValue($answers, 'team_size', 1),
            'team_skills'        => $this->extractValue($answers, 'team_skills', 'basic'),
            'process_efficiency' => $this->extractValue($answers, 'process_efficiency', 'developing'),
            'tool_usage'         => $this->extractValue($answers, 'tool_usage', 'minimal'),
            'execution_speed'    => $this->extractValue($answers, 'execution_speed', 'slow'),
            'outsourcing'        => $this->extractValue($answers, 'outsourcing', 'none'),
            'workflow_documented'=> $this->extractValue($answers, 'workflow_documented', false),
            'sector'             => $context['sector'] ?? 'general',
        ];
    }

    private function assessTeam(array $data): array {
        $sizeLabel = 'عمل فردي';
        foreach (self::TEAM_SIZE_BENCHMARKS as $benchmark) {
            if ($data['team_size'] >= $benchmark['min']) {
                $sizeLabel = $benchmark['label'];
                break;
            }
        }

        $sizeScore = min(100, $data['team_size'] * 12);

        $skillScore = match ($data['team_skills']) {
            'expert' => 90, 'advanced' => 75, 'intermediate' => 55,
            'basic' => 30, 'beginner' => 15, default => 25,
        };

        $outsourcingBonus = match ($data['outsourcing']) {
            'extensive' => 20, 'selective' => 12, 'minimal' => 5, default => 0,
        };

        $capabilityScore = ($sizeScore * 0.30) + ($skillScore * 0.55) + ($outsourcingBonus * 0.15);
        $capabilityScore = min(100, $capabilityScore);

        return [
            'team_size'        => $data['team_size'],
            'size_label'       => $sizeLabel,
            'size_score'       => round($sizeScore, 1),
            'skill_score'      => round($skillScore, 1),
            'capability_score' => round($capabilityScore, 1),
            'capability_label' => $this->getScoreLabel($capabilityScore),
        ];
    }

    private function evaluateProcesses(array $data): array {
        $efficiencyInfo = self::EFFICIENCY_LEVELS[$data['process_efficiency']]
            ?? self::EFFICIENCY_LEVELS['developing'];

        $speedScore = match ($data['execution_speed']) {
            'very_fast' => 90, 'fast' => 75, 'moderate' => 55,
            'slow' => 30, 'very_slow' => 10, default => 35,
        };

        $documentationScore = $data['workflow_documented'] ? 75 : 25;

        $executionScore = ($efficiencyInfo['score'] * 0.40) + ($speedScore * 0.40) + ($documentationScore * 0.20);

        return [
            'efficiency_level' => $efficiencyInfo['label'],
            'efficiency_score' => $efficiencyInfo['score'],
            'speed_score'      => round($speedScore, 1),
            'documentation'    => $data['workflow_documented'],
            'execution_score'  => round($executionScore, 1),
            'execution_label'  => $this->getScoreLabel($executionScore),
        ];
    }

    private function assessResources(array $data, string $sector): array {
        $toolScore = match ($data['tool_usage']) {
            'comprehensive' => 90, 'advanced' => 75, 'moderate' => 55,
            'basic' => 35, 'minimal' => 15, 'none' => 5, default => 20,
        };

        $skillScore = match ($data['team_skills']) {
            'expert' => 90, 'advanced' => 75, 'intermediate' => 55,
            'basic' => 30, default => 20,
        };

        $utilizationScore = ($toolScore * 0.45) + ($skillScore * 0.55);

        return [
            'tool_score'        => round($toolScore, 1),
            'skill_score'       => round($skillScore, 1),
            'utilization_score' => round($utilizationScore, 1),
            'utilization_label' => $this->getScoreLabel($utilizationScore),
            'sector'            => $sector,
        ];
    }

    private function calculateOverallReadiness(array $team, array $process, array $resources): float {
        return ($team['capability_score'] * 0.35)
             + ($process['execution_score'] * 0.40)
             + ($resources['utilization_score'] * 0.25);
    }
}
