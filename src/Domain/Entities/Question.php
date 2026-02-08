<?php
declare(strict_types=1);

namespace App\Domain\Entities;

class Question
{
    public function __construct(
        private string $id,
        private string $category,
        private ?string $subcategory,
        private string $questionTextAr,
        private string $questionTextEn,
        private string $questionType,
        private bool $isRequired = true,
        private string $priority = 'medium',
        private int $displayOrder = 0,
        private ?string $helpTextAr = null,
        private ?string $helpTextEn = null,
        private ?array $validationRules = null,
        private ?array $options = null,
        private ?array $metadata = null,
        private ?string $industrySpecific = null,
        private bool $isActive = true,
    ) {}

    public function getId(): string { return $this->id; }
    public function getCategory(): string { return $this->category; }
    public function getSubcategory(): ?string { return $this->subcategory; }
    public function getQuestionTextAr(): string { return $this->questionTextAr; }
    public function getQuestionTextEn(): string { return $this->questionTextEn; }
    public function getQuestionType(): string { return $this->questionType; }
    public function isRequired(): bool { return $this->isRequired; }
    public function getPriority(): string { return $this->priority; }
    public function getDisplayOrder(): int { return $this->displayOrder; }
    public function getOptions(): ?array { return $this->options; }
    public function getMetadata(): ?array { return $this->metadata; }
    public function getIndustrySpecific(): ?string { return $this->industrySpecific; }
    public function isActive(): bool { return $this->isActive; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'question_text_ar' => $this->questionTextAr,
            'question_text_en' => $this->questionTextEn,
            'question_type' => $this->questionType,
            'is_required' => $this->isRequired,
            'priority' => $this->priority,
            'display_order' => $this->displayOrder,
            'help_text_ar' => $this->helpTextAr,
            'help_text_en' => $this->helpTextEn,
            'validation_rules' => $this->validationRules,
            'options' => $this->options,
            'metadata' => $this->metadata,
            'industry_specific' => $this->industrySpecific,
            'is_active' => $this->isActive,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            category: $data['category'],
            subcategory: $data['subcategory'] ?? null,
            questionTextAr: $data['question_text_ar'],
            questionTextEn: $data['question_text_en'] ?? $data['question_text_ar'],
            questionType: $data['question_type'],
            isRequired: (bool)($data['is_required'] ?? true),
            priority: $data['priority'] ?? 'medium',
            displayOrder: (int)($data['display_order'] ?? 0),
            helpTextAr: $data['help_text_ar'] ?? null,
            helpTextEn: $data['help_text_en'] ?? null,
            validationRules: is_string($data['validation_rules'] ?? null) ? json_decode($data['validation_rules'], true) : ($data['validation_rules'] ?? null),
            options: is_string($data['options'] ?? null) ? json_decode($data['options'], true) : ($data['options'] ?? null),
            metadata: is_string($data['metadata'] ?? null) ? json_decode($data['metadata'], true) : ($data['metadata'] ?? null),
            industrySpecific: $data['industry_specific'] ?? null,
            isActive: (bool)($data['is_active'] ?? true),
        );
    }
}
