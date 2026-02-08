<?php
declare(strict_types=1);

namespace App\Core\Engines;

interface PatternDetectionInterface
{
    public function detectPatterns(array $answers, array $context = []): array;
    public function identifyStrengths(array $scores): array;
    public function identifyWeaknesses(array $scores): array;
    public function findCorrelations(array $answers): array;
}
