<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Score
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < 0.0 || $value > 100.0) {
            throw new InvalidArgumentException(
                "Score must be between 0 and 100, got: {$value}"
            );
        }

        $this->value = round($value, 2);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match (true) {
            $this->value >= 90.0 => 'ممتاز',
            $this->value >= 80.0 => 'جيد جداً',
            $this->value >= 70.0 => 'جيد',
            $this->value >= 50.0 => 'متوسط',
            $this->value >= 30.0 => 'ضعيف',
            default               => 'حرج',
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return "{$this->value} ({$this->getLabel()})";
    }
}
