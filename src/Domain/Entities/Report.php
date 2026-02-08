<?php
declare(strict_types=1);

namespace App\Domain\Entities;

class Report
{
    public function __construct(
        private string $id,
        private string $sessionId,
        private string $companyId,
        private string $reportType = 'executive_summary',
        private string $status = 'generating',
        private ?array $reportData = null,
        private ?array $scores = null,
        private ?array $recommendations = null,
        private ?array $alerts = null,
        private ?string $generatedAt = null,
        private ?string $expiresAt = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getSessionId(): string { return $this->sessionId; }
    public function getCompanyId(): string { return $this->companyId; }
    public function getReportType(): string { return $this->reportType; }
    public function getStatus(): string { return $this->status; }
    public function getReportData(): ?array { return $this->reportData; }
    public function getScores(): ?array { return $this->scores; }
    public function getRecommendations(): ?array { return $this->recommendations; }
    public function getAlerts(): ?array { return $this->alerts; }

    public function markReady(): void
    {
        $this->status = 'ready';
        $this->generatedAt = date('Y-m-d H:i:s');
    }

    public function markFailed(): void
    {
        $this->status = 'failed';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->sessionId,
            'company_id' => $this->companyId,
            'report_type' => $this->reportType,
            'status' => $this->status,
            'report_data' => $this->reportData,
            'scores' => $this->scores,
            'recommendations' => $this->recommendations,
            'alerts' => $this->alerts,
            'generated_at' => $this->generatedAt,
            'expires_at' => $this->expiresAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        $decode = fn($v) => is_string($v) ? json_decode($v, true) : $v;
        return new self(
            id: $data['id'],
            sessionId: $data['session_id'],
            companyId: $data['company_id'],
            reportType: $data['report_type'] ?? 'executive_summary',
            status: $data['status'] ?? 'generating',
            reportData: $decode($data['report_data'] ?? null),
            scores: $decode($data['scores'] ?? null),
            recommendations: $decode($data['recommendations'] ?? null),
            alerts: $decode($data['alerts'] ?? null),
            generatedAt: $data['generated_at'] ?? null,
            expiresAt: $data['expires_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
