<?php
declare(strict_types=1);

namespace App\Domain\Entities;

class Answer
{
    public function __construct(
        private string $id,
        private string $sessionId,
        private string $questionId,
        private ?string $answerValue = null,
        private ?array $answerNormalized = null,
        private float $confidenceScore = 1.00,
        private string $source = 'user',
        private int $timeSpentSeconds = 0,
        private bool $isSkipped = false,
        private ?string $skipReason = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getSessionId(): string { return $this->sessionId; }
    public function getQuestionId(): string { return $this->questionId; }
    public function getAnswerValue(): ?string { return $this->answerValue; }
    public function getAnswerNormalized(): ?array { return $this->answerNormalized; }
    public function getConfidenceScore(): float { return $this->confidenceScore; }
    public function getSource(): string { return $this->source; }
    public function getTimeSpentSeconds(): int { return $this->timeSpentSeconds; }
    public function isSkipped(): bool { return $this->isSkipped; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->sessionId,
            'question_id' => $this->questionId,
            'answer_value' => $this->answerValue,
            'answer_normalized' => $this->answerNormalized,
            'confidence_score' => $this->confidenceScore,
            'source' => $this->source,
            'time_spent_seconds' => $this->timeSpentSeconds,
            'is_skipped' => $this->isSkipped,
            'skip_reason' => $this->skipReason,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            sessionId: $data['session_id'],
            questionId: $data['question_id'],
            answerValue: $data['answer_value'] ?? null,
            answerNormalized: is_string($data['answer_normalized'] ?? null) ? json_decode($data['answer_normalized'], true) : ($data['answer_normalized'] ?? null),
            confidenceScore: (float)($data['confidence_score'] ?? 1.0),
            source: $data['source'] ?? 'user',
            timeSpentSeconds: (int)($data['time_spent_seconds'] ?? 0),
            isSkipped: (bool)($data['is_skipped'] ?? false),
            skipReason: $data['skip_reason'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
