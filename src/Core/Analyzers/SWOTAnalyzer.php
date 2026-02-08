<?php
declare(strict_types=1);

namespace App\Core\Analyzers;

class SWOTAnalyzer
{
    public function analyze(array $scores, array $answers, array $context = []): array
    {
        return [
            'strengths' => $this->identifyStrengths($scores, $answers),
            'weaknesses' => $this->identifyWeaknesses($scores, $answers),
            'opportunities' => $this->identifyOpportunities($scores, $context),
            'threats' => $this->identifyThreats($scores, $context),
        ];
    }

    private function identifyStrengths(array $scores, array $answers): array
    {
        $strengths = [];
        foreach ($scores as $dimension => $score) {
            if ($score >= 70) {
                $strengths[] = [
                    'dimension' => $dimension,
                    'score' => $score,
                    'description' => $this->getStrengthDescription($dimension, $score),
                ];
            }
        }
        return $strengths;
    }

    private function identifyWeaknesses(array $scores, array $answers): array
    {
        $weaknesses = [];
        foreach ($scores as $dimension => $score) {
            if ($score < 40) {
                $weaknesses[] = [
                    'dimension' => $dimension,
                    'score' => $score,
                    'description' => $this->getWeaknessDescription($dimension, $score),
                    'priority' => $score < 20 ? 'critical' : 'high',
                ];
            }
        }
        return $weaknesses;
    }

    private function identifyOpportunities(array $scores, array $context): array
    {
        $opportunities = [];
        $sector = $context['sector'] ?? 'general';

        $gapDimensions = array_filter($scores, fn($s) => $s >= 30 && $s < 60);
        foreach ($gapDimensions as $dimension => $score) {
            $opportunities[] = [
                'dimension' => $dimension,
                'current_score' => $score,
                'potential_gain' => 100 - $score,
                'description' => "فرصة تحسين {$dimension} من {$score}% إلى مستوى أعلى",
            ];
        }

        return $opportunities;
    }

    private function identifyThreats(array $scores, array $context): array
    {
        $threats = [];
        $criticalDimensions = array_filter($scores, fn($s) => $s < 25);

        foreach ($criticalDimensions as $dimension => $score) {
            $threats[] = [
                'dimension' => $dimension,
                'score' => $score,
                'severity' => 'high',
                'description' => "تهديد: ضعف شديد في {$dimension} قد يؤثر على المنافسة",
            ];
        }

        return $threats;
    }

    private function getStrengthDescription(string $dimension, float $score): string
    {
        $level = $score >= 90 ? 'ممتاز' : 'جيد جداً';
        return "أداء {$level} في مجال {$dimension} بنسبة {$score}%";
    }

    private function getWeaknessDescription(string $dimension, float $score): string
    {
        $level = $score < 20 ? 'حرج' : 'يحتاج تحسين';
        return "مستوى {$level} في مجال {$dimension} بنسبة {$score}% فقط";
    }
}
