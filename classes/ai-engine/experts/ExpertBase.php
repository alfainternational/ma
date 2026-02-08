<?php
/**
 * ExpertBase - Abstract Base Class for Marketing AI Expert System
 *
 * Provides common functionality for all expert modules including
 * confidence calculation, recommendation formatting, and sector benchmarks.
 */
abstract class ExpertBase {

    protected string $id;
    protected string $name;
    protected string $role;
    protected array $expertiseAreas = [];
    protected array $personality = [];
    protected float $decisionWeight = 0.5;
    protected Database $db;

    /**
     * Initialize expert with database connection and call subclass initialization.
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->initialize();
    }

    /**
     * Set up expert identity, expertise areas, personality traits, and decision weight.
     */
    abstract protected function initialize(): void;

    /**
     * Perform primary analysis using expert-specific frameworks.
     *
     * @param array $answers  Raw questionnaire answers
     * @param array $context  Business context (sector, size, stage, etc.)
     * @param array $scores   Pre-calculated scores from other systems
     * @return array Structured analysis result
     */
    abstract public function analyze(array $answers, array $context, array $scores): array;

    /**
     * Extract actionable insights from analysis results.
     *
     * @param array $analysisResult Output from analyze()
     * @return array List of formatted insights
     */
    abstract public function generateInsights(array $analysisResult): array;

    /**
     * Generate prioritized recommendations from analysis results.
     *
     * @param array $analysisResult Output from analyze()
     * @return array List of formatted recommendations
     */
    abstract public function generateRecommendations(array $analysisResult): array;

    // ─── Getters ─────────────────────────────────────────────────────────

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getExpertiseAreas(): array {
        return $this->expertiseAreas;
    }

    public function getDecisionWeight(): float {
        return $this->decisionWeight;
    }

    public function getPersonality(): array {
        return $this->personality;
    }

    // ─── Confidence Calculation ──────────────────────────────────────────

    /**
     * Calculate confidence level based on data completeness and consistency.
     *
     * @param array $dataPoints Key-value pairs of available data
     * @return float Confidence between 0.0 and 1.0
     */
    protected function calculateConfidence(array $dataPoints): float {
        if (empty($dataPoints)) {
            return 0.0;
        }

        $totalPoints = count($dataPoints);
        $filledPoints = 0;
        $consistencyScore = 1.0;

        foreach ($dataPoints as $key => $value) {
            if ($value !== null && $value !== '' && $value !== []) {
                $filledPoints++;
            }
        }

        // Base confidence from data completeness
        $completeness = $filledPoints / $totalPoints;

        // Check for internal consistency (numeric values within expected ranges)
        $numericValues = array_filter($dataPoints, 'is_numeric');
        if (count($numericValues) >= 2) {
            $values = array_values($numericValues);
            $mean = array_sum($values) / count($values);
            $variance = 0;
            foreach ($values as $v) {
                $variance += ($v - $mean) ** 2;
            }
            $variance /= count($values);
            $stdDev = sqrt($variance);
            // High deviation reduces consistency score slightly
            $cv = $mean != 0 ? $stdDev / abs($mean) : 0;
            $consistencyScore = max(0.5, 1.0 - ($cv * 0.2));
        }

        $confidence = ($completeness * 0.7) + ($consistencyScore * 0.3);

        return round(min(1.0, max(0.0, $confidence)), 2);
    }

    // ─── Formatting Helpers ──────────────────────────────────────────────

    /**
     * Format a recommendation into a standardized structure.
     *
     * @param string $title       Short recommendation title
     * @param string $description Detailed explanation
     * @param string $priority    One of: critical, high, medium, low
     * @param array  $actions     List of specific action steps
     * @return array Formatted recommendation
     */
    protected function formatRecommendation(
        string $title,
        string $description,
        string $priority,
        array $actions
    ): array {
        $priorityOrder = ['critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4];

        return [
            'expert_id'      => $this->id,
            'expert_name'    => $this->name,
            'title'          => $title,
            'description'    => $description,
            'priority'       => $priority,
            'priority_order' => $priorityOrder[$priority] ?? 5,
            'actions'        => $actions,
            'created_at'     => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format an insight into a standardized structure.
     *
     * @param string $title       Short insight title
     * @param string $description Detailed finding
     * @param string $impact      One of: positive, negative, neutral, warning
     * @param float  $confidence  Confidence level 0.0-1.0
     * @return array Formatted insight
     */
    protected function formatInsight(
        string $title,
        string $description,
        string $impact,
        float $confidence
    ): array {
        return [
            'expert_id'   => $this->id,
            'expert_name' => $this->name,
            'title'       => $title,
            'description' => $description,
            'impact'      => $impact,
            'confidence'  => round($confidence, 2),
            'created_at'  => date('Y-m-d H:i:s'),
        ];
    }

    // ─── Sector Benchmarks ───────────────────────────────────────────────

    /**
     * Retrieve sector-specific benchmark data from the database.
     *
     * @param string $sector Business sector identifier
     * @param string $metric Metric name to look up
     * @return array|null Benchmark data or null if not found
     */
    protected function getSectorBenchmark(string $sector, string $metric): ?array {
        $benchmark = $this->db->fetch(
            "SELECT * FROM sector_benchmarks WHERE sector = :sector AND metric = :metric AND is_active = 1",
            ['sector' => $sector, 'metric' => $metric]
        );

        if ($benchmark) {
            return [
                'sector'     => $benchmark['sector'],
                'metric'     => $benchmark['metric'],
                'min_value'  => (float) $benchmark['min_value'],
                'max_value'  => (float) $benchmark['max_value'],
                'avg_value'  => (float) $benchmark['avg_value'],
                'top_value'  => (float) $benchmark['top_value'],
                'unit'       => $benchmark['unit'] ?? '',
                'updated_at' => $benchmark['updated_at'] ?? null,
            ];
        }

        return null;
    }

    // ─── Utility Methods ─────────────────────────────────────────────────

    /**
     * Safely extract a value from a nested array using dot notation.
     *
     * @param array  $array   Source array
     * @param string $key     Dot-notated key path (e.g., "marketing.budget")
     * @param mixed  $default Default value if key not found
     * @return mixed
     */
    protected function extractValue(array $array, string $key, mixed $default = null): mixed {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    /**
     * Normalize a score to a 0-100 range.
     *
     * @param float $value Current value
     * @param float $min   Minimum expected value
     * @param float $max   Maximum expected value
     * @return float Normalized score 0-100
     */
    protected function normalizeScore(float $value, float $min, float $max): float {
        if ($max === $min) {
            return 50.0;
        }
        $normalized = (($value - $min) / ($max - $min)) * 100;
        return round(max(0, min(100, $normalized)), 1);
    }

    /**
     * Get a rating label for a numeric score.
     *
     * @param float $score Score 0-100
     * @return string Arabic rating label
     */
    protected function getScoreLabel(float $score): string {
        return match (true) {
            $score >= 90 => 'ممتاز',
            $score >= 75 => 'جيد جداً',
            $score >= 60 => 'جيد',
            $score >= 40 => 'متوسط',
            $score >= 20 => 'ضعيف',
            default      => 'حرج',
        };
    }

    /**
     * Build the standard analysis result envelope.
     *
     * @param array $sections  Named analysis sections
     * @param array $scores    Calculated scores
     * @param array $insights  Generated insights
     * @param array $recommendations Generated recommendations
     * @param float $confidence Overall confidence
     * @return array Standardized result structure
     */
    protected function buildResult(
        array $sections,
        array $scores,
        array $insights,
        array $recommendations,
        float $confidence
    ): array {
        return [
            'expert_id'       => $this->id,
            'expert_name'     => $this->name,
            'expert_role'     => $this->role,
            'decision_weight' => $this->decisionWeight,
            'sections'        => $sections,
            'scores'          => $scores,
            'insights'        => $insights,
            'recommendations' => $recommendations,
            'confidence'      => $confidence,
            'analyzed_at'     => date('Y-m-d H:i:s'),
        ];
    }
}
