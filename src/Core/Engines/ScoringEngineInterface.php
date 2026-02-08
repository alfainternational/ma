<?php
declare(strict_types=1);

namespace App\Core\Engines;

interface ScoringEngineInterface
{
    public function calculateScores(array $answers, array $context = []): array;
    public function calculateDimensionScore(string $dimension, array $answers): float;
    public function normalizeScore(float $rawScore, float $min = 0, float $max = 100): float;
    public function getMaturityLevel(float $score): string;
}
