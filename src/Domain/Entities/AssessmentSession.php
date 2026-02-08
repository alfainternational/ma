<?php
declare(strict_types=1);

namespace App\Domain\Entities;

class AssessmentSession
{
    public function __construct(
        private string $id,
        private string $companyId,
        private string $userId,
        private ?string $sessionName = null,
        private string $sessionType = 'full',
        private string $status = 'draft',
        private ?string $currentQuestionId = null,
        private int $questionsAnswered = 0,
        private int $questionsTotal = 0,
        private float $progressPercentage = 0.0,
        private ?string $startedAt = null,
        private ?string $completedAt = null,
        private array $contextData = [],
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getCompanyId(): string { return $this->companyId; }
    public function getUserId(): string { return $this->userId; }
    public function getSessionName(): ?string { return $this->sessionName; }
    public function getSessionType(): string { return $this->sessionType; }
    public function getStatus(): string { return $this->status; }
    public function getCurrentQuestionId(): ?string { return $this->currentQuestionId; }
    public function getQuestionsAnswered(): int { return $this->questionsAnswered; }
    public function getQuestionsTotal(): int { return $this->questionsTotal; }
    public function getProgressPercentage(): float { return $this->progressPercentage; }
    public function getStartedAt(): ?string { return $this->startedAt; }
    public function getCompletedAt(): ?string { return $this->completedAt; }
    public function getContextData(): array { return $this->contextData; }

    public function start(): void
    {
        $this->status = 'in_progress';
        $this->startedAt = date('Y-m-d H:i:s');
    }

    public function complete(): void
    {
        $this->status = 'completed';
        $this->completedAt = date('Y-m-d H:i:s');
        $this->progressPercentage = 100.0;
    }

    public function abandon(): void
    {
        $this->status = 'abandoned';
    }

    public function advanceQuestion(string $questionId, int $answered, int $total): void
    {
        $this->currentQuestionId = $questionId;
        $this->questionsAnswered = $answered;
        $this->questionsTotal = $total;
        $this->progressPercentage = $total > 0 ? round(($answered / $total) * 100, 2) : 0.0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'session_name' => $this->sessionName,
            'session_type' => $this->sessionType,
            'status' => $this->status,
            'current_question_id' => $this->currentQuestionId,
            'questions_answered' => $this->questionsAnswered,
            'questions_total' => $this->questionsTotal,
            'progress_percentage' => $this->progressPercentage,
            'started_at' => $this->startedAt,
            'completed_at' => $this->completedAt,
            'context_data' => $this->contextData,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            companyId: $data['company_id'],
            userId: $data['user_id'],
            sessionName: $data['session_name'] ?? null,
            sessionType: $data['session_type'] ?? 'full',
            status: $data['status'] ?? 'draft',
            currentQuestionId: $data['current_question_id'] ?? null,
            questionsAnswered: (int)($data['questions_answered'] ?? 0),
            questionsTotal: (int)($data['questions_total'] ?? 0),
            progressPercentage: (float)($data['progress_percentage'] ?? 0.0),
            startedAt: $data['started_at'] ?? null,
            completedAt: $data['completed_at'] ?? null,
            contextData: is_string($data['context_data'] ?? null) ? json_decode($data['context_data'], true) ?? [] : ($data['context_data'] ?? []),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
