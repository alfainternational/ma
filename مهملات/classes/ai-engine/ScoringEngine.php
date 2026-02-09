<?php
/**
 * Scoring Engine - محرك التسجيل والتقييم
 * Scores businesses on 5 dimensions (0-100 each)
 */
class ScoringEngine {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function calculateAllScores(array $answers, array $context): array {
        $answersMap = $this->mapAnswers($answers);

        $digital = $this->calculateDigitalMaturity($answersMap);
        $marketing = $this->calculateMarketingMaturity($answersMap);
        $organizational = $this->calculateOrganizationalReadiness($answersMap);
        $risk = $this->calculateRiskAssessment($answersMap, $context);
        $opportunity = $this->calculateOpportunityScore($answersMap, $context);

        $overall = round(
            ($digital['score'] * 0.25) +
            ($marketing['score'] * 0.25) +
            ($organizational['score'] * 0.20) +
            ((100 - $risk['score']) * 0.15) +
            ($opportunity['score'] * 0.15)
        );

        return [
            'overall' => $overall,
            'maturity_level' => $this->getMaturityLevel($overall),
            'digital_maturity' => $digital['score'],
            'digital_details' => $digital,
            'marketing_maturity' => $marketing['score'],
            'marketing_details' => $marketing,
            'organizational_readiness' => $organizational['score'],
            'organizational_details' => $organizational,
            'risk_score' => $risk['score'],
            'risk_breakdown' => $risk,
            'opportunity_score' => $opportunity['score'],
            'opportunity_details' => $opportunity,
        ];
    }

    public function calculateDigitalMaturity(array $answers): array {
        $components = [
            'website_quality' => ['weight' => 0.25, 'score' => 0, 'factors' => [
                'has_website' => $this->getBool($answers, 'has_website') ? 20 : 0,
                'mobile_responsive' => $this->getBool($answers, 'mobile_responsive') ? 15 : 0,
                'has_analytics' => $this->getBool($answers, 'uses_analytics') ? 15 : 0,
                'seo_optimized' => $this->getScale($answers, 'seo_efforts', 15),
                'secure_https' => $this->getBool($answers, 'has_ssl') ? 10 : 0,
                'conversion_tracking' => $this->getBool($answers, 'conversion_tracking') ? 15 : 0,
                'fast_loading' => $this->getScale($answers, 'page_speed', 10),
            ]],
            'social_media_presence' => ['weight' => 0.20, 'score' => 0, 'factors' => [
                'active_platforms' => min(25, ($this->getNumeric($answers, 'active_platforms_count') ?? 0) * 5),
                'consistent_posting' => $this->getScale($answers, 'posting_frequency', 20),
                'high_engagement' => $this->getScale($answers, 'engagement_rate', 25),
                'growing_followers' => $this->getScale($answers, 'follower_growth', 15),
                'uses_paid_social' => $this->getBool($answers, 'uses_paid_social') ? 15 : 0,
            ]],
            'digital_advertising' => ['weight' => 0.20, 'score' => 0, 'factors' => [
                'uses_google_ads' => $this->getBool($answers, 'uses_google_ads') ? 25 : 0,
                'uses_social_ads' => $this->getBool($answers, 'uses_social_ads') ? 25 : 0,
                'tracks_roi' => $this->getBool($answers, 'tracks_ad_roi') ? 30 : 0,
                'optimizes_campaigns' => $this->getScale($answers, 'campaign_optimization', 20),
            ]],
            'email_marketing' => ['weight' => 0.15, 'score' => 0, 'factors' => [
                'has_email_list' => $this->getBool($answers, 'has_email_list') ? 25 : 0,
                'regular_campaigns' => $this->getBool($answers, 'email_campaigns') ? 25 : 0,
                'segmented_lists' => $this->getBool($answers, 'email_segmentation') ? 25 : 0,
                'automated_flows' => $this->getBool($answers, 'email_automation') ? 25 : 0,
            ]],
            'analytics_usage' => ['weight' => 0.20, 'score' => 0, 'factors' => [
                'uses_analytics' => $this->getBool($answers, 'uses_analytics') ? 20 : 0,
                'tracks_kpis' => $this->getBool($answers, 'tracks_kpis') ? 25 : 0,
                'data_driven' => $this->getScale($answers, 'data_driven_decisions', 30),
                'regular_reporting' => $this->getBool($answers, 'regular_reporting') ? 25 : 0,
            ]],
        ];

        $totalScore = 0;
        foreach ($components as $name => &$component) {
            $component['score'] = min(100, array_sum($component['factors']));
            $totalScore += $component['score'] * $component['weight'];
        }

        return [
            'score' => round($totalScore),
            'level' => $this->getMaturityLevel(round($totalScore)),
            'components' => $components,
        ];
    }

    public function calculateMarketingMaturity(array $answers): array {
        $strategyScore = 0;
        $strategyScore += $this->getBool($answers, 'has_marketing_plan') ? 20 : 0;
        $strategyScore += $this->getBool($answers, 'clear_target_audience') ? 20 : 0;
        $strategyScore += $this->getBool($answers, 'defined_positioning') ? 20 : 0;
        $strategyScore += $this->getScale($answers, 'measurable_goals', 20);
        $strategyScore += $this->getBool($answers, 'documented_processes') ? 20 : 0;

        $executionScore = 0;
        $executionScore += $this->getScale($answers, 'consistent_branding', 20);
        $executionScore += $this->getBool($answers, 'regular_campaigns') ? 20 : 0;
        $executionScore += min(20, ($this->getNumeric($answers, 'channels_used') ?? 0) * 4);
        $executionScore += $this->getScale($answers, 'content_quality', 20);
        $executionScore += $this->getScale($answers, 'customer_engagement', 20);

        $measurementScore = 0;
        $measurementScore += $this->getBool($answers, 'tracks_metrics') ? 20 : 0;
        $measurementScore += $this->getBool($answers, 'calculates_roi') ? 25 : 0;
        $measurementScore += $this->getBool($answers, 'uses_attribution') ? 20 : 0;
        $measurementScore += $this->getBool($answers, 'regular_reporting') ? 15 : 0;
        $measurementScore += $this->getScale($answers, 'data_driven_optimization', 20);

        $total = round(
            min(100, $strategyScore) * 0.30 +
            min(100, $executionScore) * 0.40 +
            min(100, $measurementScore) * 0.30
        );

        return [
            'score' => $total,
            'level' => $this->getMaturityLevel($total),
            'strategy_clarity' => min(100, $strategyScore),
            'execution_quality' => min(100, $executionScore),
            'measurement_capability' => min(100, $measurementScore),
        ];
    }

    public function calculateOrganizationalReadiness(array $answers): array {
        $teamScore = 0;
        $teamScore += min(30, ($this->getNumeric($answers, 'marketing_team_size') ?? 0) * 10);
        $teamScore += $this->getScale($answers, 'team_skills', 35);
        $teamScore += $this->getScale($answers, 'team_training', 35);

        $budgetScore = 0;
        $budgetPercent = $this->getNumeric($answers, 'marketing_budget_percent') ?? 0;
        if ($budgetPercent >= 10) $budgetScore = 100;
        elseif ($budgetPercent >= 5) $budgetScore = 70;
        elseif ($budgetPercent >= 2) $budgetScore = 40;
        elseif ($budgetPercent > 0) $budgetScore = 20;

        $leadershipScore = $this->getScale($answers, 'leadership_support', 100);
        $processScore = $this->getScale($answers, 'process_maturity', 100);

        $total = round(
            min(100, $teamScore) * 0.35 +
            $budgetScore * 0.30 +
            $leadershipScore * 0.20 +
            $processScore * 0.15
        );

        return [
            'score' => $total,
            'team_capability' => min(100, $teamScore),
            'budget_adequacy' => $budgetScore,
            'leadership_support' => $leadershipScore,
            'process_maturity' => $processScore,
        ];
    }

    public function calculateRiskAssessment(array $answers, array $context): array {
        $financial = 0;
        $revTrend = $answers['revenue_trend'] ?? '';
        if ($revTrend === 'declining') $financial += 4;
        elseif ($revTrend === 'stable') $financial += 1;
        $financial += ($this->getNumeric($answers, 'profit_margin') ?? 20) < 10 ? 3 : 0;
        $financial += ($this->getNumeric($answers, 'marketing_budget_percent') ?? 5) > 30 ? 3 : 0;

        $competitive = 0;
        $compLevel = $answers['competition_level'] ?? '';
        if ($compLevel === 'very_high' || $compLevel === 'high') $competitive += 4;
        $competitive += $this->getScale($answers, 'competitor_strength', 3);
        $competitive += $this->getScale($answers, 'differentiation', 3, true);

        $execution = 0;
        $execution += ($this->getNumeric($answers, 'marketing_team_size') ?? 1) < 2 ? 3 : 0;
        $execution += $this->getScale($answers, 'skill_gaps', 4);
        $execution += $this->getScale($answers, 'resource_limitations', 3);

        $market = 0;
        $marketTrend = $answers['market_trend'] ?? '';
        if ($marketTrend === 'declining') $market += 4;
        $market += $this->getScale($answers, 'regulatory_risk', 3);
        $market += $this->getScale($answers, 'tech_disruption_risk', 3);

        $overallRisk = round(
            min(10, $financial) * 0.30 +
            min(10, $competitive) * 0.25 +
            min(10, $execution) * 0.25 +
            min(10, $market) * 0.20
        );

        $riskScore = round($overallRisk * 10); // Convert to 0-100

        return [
            'score' => min(100, $riskScore),
            'level' => $riskScore >= 70 ? 'critical' : ($riskScore >= 50 ? 'high' : ($riskScore >= 30 ? 'medium' : 'low')),
            'financial' => min(10, $financial),
            'competitive' => min(10, $competitive),
            'execution' => min(10, $execution),
            'market' => min(10, $market),
        ];
    }

    public function calculateOpportunityScore(array $answers, array $context): array {
        $growth = 0;
        $growth += ($answers['market_trend'] ?? '') === 'growing' ? 4 : 0;
        $growth += $this->getScale($answers, 'scalability', 3);
        $growth += $this->getScale($answers, 'fundamentals_strength', 3);

        $marketOpp = 0;
        $marketOpp += $this->getScale($answers, 'market_gaps', 4);
        $marketOpp += $this->getScale($answers, 'emerging_trends', 3);
        $marketOpp += ($answers['competition_level'] ?? '') === 'low' ? 3 : 0;

        $compAdv = 0;
        $compAdv += $this->getScale($answers, 'unique_offering', 3);
        $compAdv += $this->getScale($answers, 'brand_strength', 3);
        $compAdv += $this->getScale($answers, 'customer_loyalty', 4);

        $overallOpp = round(
            min(10, $growth) * 0.35 +
            min(10, $marketOpp) * 0.30 +
            min(10, $compAdv) * 0.35
        );

        $oppScore = round($overallOpp * 10);

        return [
            'score' => min(100, $oppScore),
            'growth_potential' => min(10, $growth),
            'market_opportunity' => min(10, $marketOpp),
            'competitive_advantage' => min(10, $compAdv),
        ];
    }

    public function getMaturityLevel(int $score): string {
        if ($score >= 76) return 'expert';
        if ($score >= 51) return 'advanced';
        if ($score >= 26) return 'developing';
        return 'beginner';
    }

    public function getBenchmarkComparison(int $score, string $sector, string $dimension): array {
        $benchFile = BASE_PATH . '/data/benchmarks.json';
        if (!file_exists($benchFile)) return ['score' => $score, 'benchmark' => 50, 'position' => 'average'];

        $benchmarks = json_decode(file_get_contents($benchFile), true);
        $sectorBench = $benchmarks[$sector] ?? $benchmarks['retail'] ?? [];
        $benchmark = $sectorBench[$dimension . '_benchmark'] ?? 50;

        $position = 'average';
        if ($score > $benchmark * 1.2) $position = 'above_average';
        elseif ($score < $benchmark * 0.8) $position = 'below_average';

        return ['score' => $score, 'benchmark' => $benchmark, 'position' => $position];
    }

    public function saveScores(int $sessionId, array $scores): void {
        $existing = $this->db->fetch(
            "SELECT id FROM analysis_results WHERE session_id = :sid",
            ['sid' => $sessionId]
        );

        if ($existing) {
            $this->db->update('analysis_results', [
                'scores' => json_encode($scores),
            ], 'session_id = :sid', ['sid' => $sessionId]);
        } else {
            $this->db->insert('analysis_results', [
                'session_id' => $sessionId,
                'scores' => json_encode($scores),
                'insights' => json_encode([]),
                'expert_analysis' => json_encode([]),
            ]);
        }
    }

    // Helper methods
    private function mapAnswers(array $answers): array {
        $map = [];
        foreach ($answers as $a) {
            $key = $a['question_id'] ?? '';
            $fieldMap = $a['field_mapping'] ?? $key;
            $map[$fieldMap] = $a['answer_value'] ?? null;
            $map[$key] = $a['answer_value'] ?? null;
        }
        return $map;
    }

    private function getBool(array $answers, string $key): bool {
        $val = $answers[$key] ?? null;
        return in_array($val, ['yes', 'true', '1', 'نعم', true], true);
    }

    private function getNumeric(array $answers, string $key): ?float {
        $val = $answers[$key] ?? null;
        return $val !== null && is_numeric($val) ? (float)$val : null;
    }

    private function getScale(array $answers, string $key, int $maxPoints, bool $invert = false): int {
        $val = $this->getNumeric($answers, $key);
        if ($val === null) return 0;
        $normalized = min(1, max(0, $val / 10));
        if ($invert) $normalized = 1 - $normalized;
        return round($normalized * $maxPoints);
    }
}
