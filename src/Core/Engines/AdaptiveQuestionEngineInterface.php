<?php
declare(strict_types=1);

namespace App\Core\Engines;

interface AdaptiveQuestionEngineInterface
{
    public function getNextQuestion(string $sessionId, array $previousAnswers = []): ?array;
    public function shouldSkipQuestion(array $question, array $previousAnswers): bool;
    public function shouldDeepDive(array $question, array $answer): bool;
    public function calculateQuestionPriority(array $question, array $context): float;
}
