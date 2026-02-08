<?php
declare(strict_types=1);

namespace App\Core\Experts;

interface ExpertInterface
{
    public function getName(): string;
    public function getNameAr(): string;
    public function getRole(): string;
    public function analyze(array $answers, array $context = []): array;
    public function generateInsights(array $analysisResult): array;
    public function getRecommendations(array $analysisResult): array;
}
