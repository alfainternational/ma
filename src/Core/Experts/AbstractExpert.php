<?php
declare(strict_types=1);

namespace App\Core\Experts;

abstract class AbstractExpert implements ExpertInterface
{
    protected string $name;
    protected string $nameAr;
    protected string $role;
    protected array $weights = [];

    public function getName(): string { return $this->name; }
    public function getNameAr(): string { return $this->nameAr; }
    public function getRole(): string { return $this->role; }

    abstract public function analyze(array $answers, array $context = []): array;

    public function generateInsights(array $analysisResult): array
    {
        $insights = [];
        $scores = $analysisResult['scores'] ?? [];

        foreach ($scores as $dimension => $score) {
            if ($score < 40) {
                $insights[] = [
                    'type' => 'weakness',
                    'dimension' => $dimension,
                    'score' => $score,
                    'expert' => $this->name,
                    'priority' => 'high',
                ];
            } elseif ($score > 75) {
                $insights[] = [
                    'type' => 'strength',
                    'dimension' => $dimension,
                    'score' => $score,
                    'expert' => $this->name,
                    'priority' => 'info',
                ];
            }
        }

        return $insights;
    }

    public function getRecommendations(array $analysisResult): array
    {
        return [];
    }

    protected function calculateWeightedScore(array $scores): float
    {
        if (empty($this->weights) || empty($scores)) {
            return 0.0;
        }

        $total = 0.0;
        $weightSum = 0.0;

        foreach ($this->weights as $key => $weight) {
            if (isset($scores[$key])) {
                $total += $scores[$key] * $weight;
                $weightSum += $weight;
            }
        }

        return $weightSum > 0 ? round($total / $weightSum, 2) : 0.0;
    }

    protected function classifyMaturity(float $score): string
    {
        return match(true) {
            $score >= 80 => 'متقدم',
            $score >= 60 => 'متوسط متقدم',
            $score >= 40 => 'متوسط',
            $score >= 20 => 'مبتدئ',
            default => 'غير مبتدئ',
        };
    }
}
