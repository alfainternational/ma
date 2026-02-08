<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\Answer;
use App\Domain\Entities\Question;
use App\Infrastructure\Persistence\Database;
use App\Shared\Exceptions\NotFoundException;
use App\Shared\Exceptions\BusinessLogicException;

final class AnalysisService
{
    /**
     * The five maturity dimensions used for scoring.
     */
    private const DIMENSIONS = [
        'digital'        => 'Digital Maturity',
        'marketing'      => 'Marketing Effectiveness',
        'organizational' => 'Organizational Readiness',
        'risk'           => 'Risk Awareness',
        'opportunity'    => 'Opportunity Exploitation',
    ];

    /**
     * Mapping from question categories to maturity dimensions.
     */
    private const CATEGORY_DIMENSION_MAP = [
        'digital_presence'    => 'digital',
        'digital_tools'       => 'digital',
        'data_analytics'      => 'digital',
        'automation'          => 'digital',
        'strategy'            => 'marketing',
        'branding'            => 'marketing',
        'content'             => 'marketing',
        'campaigns'           => 'marketing',
        'customer_engagement' => 'marketing',
        'team'                => 'organizational',
        'processes'           => 'organizational',
        'budget'              => 'organizational',
        'leadership'          => 'organizational',
        'compliance'          => 'risk',
        'security'            => 'risk',
        'market_risk'         => 'risk',
        'growth'              => 'opportunity',
        'innovation'          => 'opportunity',
        'partnerships'        => 'opportunity',
        'market_expansion'    => 'opportunity',
    ];

    public function __construct(
        private readonly Database $db,
    ) {}

    /**
     * Run a full analysis on a completed assessment session.
     *
     * @return array{session_id: string, maturity_scores: array, swot: array, patterns: array, overall_score: float, overall_label: string, analyzed_at: string}
     *
     * @throws NotFoundException     When the session does not exist.
     * @throws BusinessLogicException When the session has not been completed.
     */
    public function analyzeSession(string $sessionId): array
    {
        $session = $this->fetchSession($sessionId);

        if ($session['status'] !== 'completed') {
            throw new BusinessLogicException(
                'Analysis can only be performed on completed sessions'
            );
        }

        $maturityScores = $this->calculateMaturityScores($sessionId);
        $swot           = $this->generateSWOT($sessionId);
        $patterns       = $this->detectPatterns($sessionId);

        $dimensionValues = array_column($maturityScores, 'score');
        $overallScore    = count($dimensionValues) > 0
            ? round(array_sum($dimensionValues) / count($dimensionValues), 2)
            : 0.0;

        $overallLabel = $this->scoreLabel($overallScore);

        $analysisResult = [
            'session_id'      => $sessionId,
            'maturity_scores' => $maturityScores,
            'swot'            => $swot,
            'patterns'        => $patterns,
            'overall_score'   => $overallScore,
            'overall_label'   => $overallLabel,
            'analyzed_at'     => date('Y-m-d H:i:s'),
        ];

        $this->persistAnalysis($sessionId, $analysisResult);

        return $analysisResult;
    }

    /**
     * Calculate maturity scores across the five dimensions.
     *
     * @return array<string, array{dimension: string, label: string, score: float, score_label: string, question_count: int}>
     *
     * @throws NotFoundException When the session does not exist.
     */
    public function calculateMaturityScores(string $sessionId): array
    {
        $this->fetchSession($sessionId);

        $answers   = $this->fetchAnswersWithQuestions($sessionId);
        $buckets   = $this->bucketByDimension($answers);
        $scores    = [];

        foreach (self::DIMENSIONS as $key => $label) {
            $items = $buckets[$key] ?? [];

            if (count($items) === 0) {
                $scores[$key] = [
                    'dimension'      => $key,
                    'label'          => $label,
                    'score'          => 0.0,
                    'score_label'    => $this->scoreLabel(0.0),
                    'question_count' => 0,
                ];
                continue;
            }

            $total = 0.0;

            foreach ($items as $item) {
                $total += $this->extractNumericScore($item);
            }

            $score = round(($total / count($items)) * 20, 2); // normalize to 0-100

            $scores[$key] = [
                'dimension'      => $key,
                'label'          => $label,
                'score'          => min(100.0, $score),
                'score_label'    => $this->scoreLabel($score),
                'question_count' => count($items),
            ];
        }

        return $scores;
    }

    /**
     * Build a SWOT analysis from session answers.
     *
     * @return array{strengths: string[], weaknesses: string[], opportunities: string[], threats: string[]}
     *
     * @throws NotFoundException When the session does not exist.
     */
    public function generateSWOT(string $sessionId): array
    {
        $this->fetchSession($sessionId);

        $answers = $this->fetchAnswersWithQuestions($sessionId);

        $swot = [
            'strengths'     => [],
            'weaknesses'    => [],
            'opportunities' => [],
            'threats'       => [],
        ];

        foreach ($answers as $item) {
            $score    = $this->extractNumericScore($item);
            $category = $item['category'] ?? 'general';
            $text     = $item['question_text_en'] ?? $item['question_text_ar'] ?? '';
            $dimension = self::CATEGORY_DIMENSION_MAP[$category] ?? null;

            if ($score >= 4.0) {
                $swot['strengths'][] = [
                    'area'      => $category,
                    'detail'    => $text,
                    'score'     => $score,
                    'dimension' => $dimension,
                ];
            } elseif ($score <= 2.0) {
                $swot['weaknesses'][] = [
                    'area'      => $category,
                    'detail'    => $text,
                    'score'     => $score,
                    'dimension' => $dimension,
                ];
            }

            if ($dimension === 'opportunity' && $score <= 3.0) {
                $swot['opportunities'][] = [
                    'area'   => $category,
                    'detail' => "Untapped potential in: {$text}",
                    'score'  => $score,
                ];
            }

            if ($dimension === 'risk' && $score <= 2.0) {
                $swot['threats'][] = [
                    'area'   => $category,
                    'detail' => "Risk exposure in: {$text}",
                    'score'  => $score,
                ];
            }
        }

        // Sort each quadrant: strengths descending, weaknesses ascending
        usort($swot['strengths'], fn(array $a, array $b): int => $b['score'] <=> $a['score']);
        usort($swot['weaknesses'], fn(array $a, array $b): int => $a['score'] <=> $b['score']);

        return $swot;
    }

    /**
     * Detect patterns and clusters within the session answers.
     *
     * @return array{consistently_strong: string[], consistently_weak: string[], high_variance: string[], skipped_areas: string[], time_analysis: array}
     *
     * @throws NotFoundException When the session does not exist.
     */
    public function detectPatterns(string $sessionId): array
    {
        $this->fetchSession($sessionId);

        $answers = $this->fetchAnswersWithQuestions($sessionId);
        $buckets = $this->bucketByDimension($answers);

        $consistentlyStrong = [];
        $consistentlyWeak   = [];
        $highVariance       = [];

        foreach ($buckets as $dimension => $items) {
            if (count($items) < 2) {
                continue;
            }

            $scores = array_map(fn(array $i): float => $this->extractNumericScore($i), $items);
            $avg    = array_sum($scores) / count($scores);

            $variance = array_sum(array_map(
                fn(float $s): float => ($s - $avg) ** 2,
                $scores,
            )) / count($scores);

            if ($avg >= 4.0) {
                $consistentlyStrong[] = $dimension;
            } elseif ($avg <= 2.0) {
                $consistentlyWeak[] = $dimension;
            }

            if ($variance > 1.5) {
                $highVariance[] = $dimension;
            }
        }

        // Skipped areas
        $skippedAreas = $this->db->fetchAll(
            'SELECT DISTINCT q.category
             FROM answers a
             JOIN questions q ON q.id = a.question_id
             WHERE a.session_id = :session_id AND a.is_skipped = 1',
            ['session_id' => $sessionId],
        );

        // Time analysis
        $timeRows = $this->db->fetchAll(
            'SELECT q.category, AVG(a.time_spent_seconds) AS avg_time, MAX(a.time_spent_seconds) AS max_time
             FROM answers a
             JOIN questions q ON q.id = a.question_id
             WHERE a.session_id = :session_id AND a.time_spent_seconds > 0
             GROUP BY q.category
             ORDER BY avg_time DESC',
            ['session_id' => $sessionId],
        );

        return [
            'consistently_strong' => $consistentlyStrong,
            'consistently_weak'   => $consistentlyWeak,
            'high_variance'       => $highVariance,
            'skipped_areas'       => array_column($skippedAreas, 'category'),
            'time_analysis'       => $timeRows,
        ];
    }

    /**
     * Compare session scores against sector-specific benchmarks.
     *
     * @return array<string, array{dimension: string, company_score: float, benchmark_score: float, delta: float, percentile: ?float}>
     *
     * @throws NotFoundException When the session does not exist.
     */
    public function compareWithBenchmarks(string $sessionId, string $sector): array
    {
        $maturityScores = $this->calculateMaturityScores($sessionId);

        $benchmarks = $this->loadSectorBenchmarks($sector);

        $comparison = [];

        foreach (self::DIMENSIONS as $key => $label) {
            $companyScore   = $maturityScores[$key]['score'] ?? 0.0;
            $benchmarkScore = $benchmarks[$key]['avg_score'] ?? 0.0;
            $delta          = round($companyScore - $benchmarkScore, 2);

            $comparison[$key] = [
                'dimension'       => $label,
                'company_score'   => $companyScore,
                'benchmark_score' => $benchmarkScore,
                'delta'           => $delta,
                'percentile'      => $this->calculatePercentile(
                    $companyScore,
                    $benchmarks[$key]['scores'] ?? [],
                ),
            ];
        }

        return $comparison;
    }

    // ---------------------------------------------------------------
    //  Private helpers
    // ---------------------------------------------------------------

    /**
     * Fetch and validate that a session row exists.
     *
     * @throws NotFoundException When the session does not exist.
     */
    private function fetchSession(string $sessionId): array
    {
        $session = $this->db->fetch(
            'SELECT * FROM assessment_sessions WHERE id = :id',
            ['id' => $sessionId],
        );

        if ($session === null) {
            throw new NotFoundException("Session not found: {$sessionId}");
        }

        return $session;
    }

    /**
     * Retrieve all answers joined with their questions for a session.
     */
    private function fetchAnswersWithQuestions(string $sessionId): array
    {
        return $this->db->fetchAll(
            'SELECT a.*, q.category, q.subcategory, q.question_type,
                    q.question_text_ar, q.question_text_en, q.options
             FROM answers a
             JOIN questions q ON q.id = a.question_id
             WHERE a.session_id = :session_id
               AND a.is_skipped = 0
             ORDER BY q.display_order ASC',
            ['session_id' => $sessionId],
        );
    }

    /**
     * Group answer rows into dimension buckets using the category-to-dimension mapping.
     *
     * @return array<string, array<int, array>>
     */
    private function bucketByDimension(array $answers): array
    {
        $buckets = [];

        foreach ($answers as $item) {
            $dimension = self::CATEGORY_DIMENSION_MAP[$item['category'] ?? ''] ?? null;

            if ($dimension === null) {
                continue;
            }

            $buckets[$dimension][] = $item;
        }

        return $buckets;
    }

    /**
     * Extract a numeric score (0-5 scale) from an answer row.
     */
    private function extractNumericScore(array $item): float
    {
        $normalized = $item['answer_normalized'] ?? null;

        if (is_string($normalized)) {
            $normalized = json_decode($normalized, true);
        }

        if (is_array($normalized)) {
            if (isset($normalized['numeric_value'])) {
                return (float) $normalized['numeric_value'];
            }

            if (isset($normalized['boolean'])) {
                return $normalized['boolean'] ? 5.0 : 1.0;
            }

            if (isset($normalized['percentage'])) {
                return (float) $normalized['percentage'] / 20.0; // map 0-100 to 0-5
            }
        }

        // Fallback: attempt to interpret the raw answer value
        $raw = $item['answer_value'] ?? null;

        if ($raw !== null && is_numeric($raw)) {
            return (float) $raw;
        }

        return 0.0;
    }

    /**
     * Translate a 0-100 score into a human-readable label.
     */
    private function scoreLabel(float $score): string
    {
        return match (true) {
            $score >= 90.0 => 'Excellent',
            $score >= 80.0 => 'Very Good',
            $score >= 70.0 => 'Good',
            $score >= 50.0 => 'Average',
            $score >= 30.0 => 'Below Average',
            default        => 'Critical',
        };
    }

    /**
     * Load benchmark data for a given sector from the database.
     *
     * @return array<string, array{avg_score: float, scores: float[]}>
     */
    private function loadSectorBenchmarks(string $sector): array
    {
        $rows = $this->db->fetchAll(
            'SELECT dimension, avg_score, score_distribution
             FROM sector_benchmarks
             WHERE sector = :sector',
            ['sector' => $sector],
        );

        $benchmarks = [];

        foreach ($rows as $row) {
            $distribution = is_string($row['score_distribution'] ?? null)
                ? json_decode($row['score_distribution'], true) ?? []
                : ($row['score_distribution'] ?? []);

            $benchmarks[$row['dimension']] = [
                'avg_score' => (float) $row['avg_score'],
                'scores'    => array_map('floatval', $distribution),
            ];
        }

        return $benchmarks;
    }

    /**
     * Calculate what percentile a given score falls into within a distribution.
     */
    private function calculatePercentile(float $score, array $distribution): ?float
    {
        if (count($distribution) === 0) {
            return null;
        }

        sort($distribution);
        $below = count(array_filter($distribution, fn(float $s): bool => $s < $score));

        return round(($below / count($distribution)) * 100, 1);
    }

    /**
     * Store the computed analysis result for later retrieval.
     */
    private function persistAnalysis(string $sessionId, array $result): void
    {
        $existing = $this->db->fetch(
            'SELECT id FROM analysis_results WHERE session_id = :session_id',
            ['session_id' => $sessionId],
        );

        $now = date('Y-m-d H:i:s');
        $encoded = json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        if ($existing !== null) {
            $this->db->update(
                'analysis_results',
                [
                    'result_data' => $encoded,
                    'overall_score' => $result['overall_score'],
                    'updated_at' => $now,
                ],
                'session_id = :session_id',
                ['session_id' => $sessionId],
            );
        } else {
            $this->db->insert('analysis_results', [
                'id'            => \App\Shared\Utils\UUID::generate(),
                'session_id'    => $sessionId,
                'result_data'   => $encoded,
                'overall_score' => $result['overall_score'],
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }
}
