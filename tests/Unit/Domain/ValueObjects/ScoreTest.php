<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Score;
use PHPUnit\Framework\TestCase;

class ScoreTest extends TestCase
{
    public function testValidScore(): void
    {
        $score = new Score(75.5);
        $this->assertEquals(75.5, $score->getValue());
    }

    public function testScoreAtBoundaries(): void
    {
        $zero = new Score(0);
        $hundred = new Score(100);

        $this->assertEquals(0, $zero->getValue());
        $this->assertEquals(100, $hundred->getValue());
    }

    public function testNegativeScoreThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Score(-1);
    }

    public function testScoreAbove100ThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Score(101);
    }

    public function testGetLabel(): void
    {
        $expert = new Score(95);
        $advanced = new Score(80);
        $intermediate = new Score(55);
        $developing = new Score(30);
        $beginner = new Score(10);

        $this->assertEquals('خبير', $expert->getLabel());
        $this->assertEquals('متقدم', $advanced->getLabel());
        $this->assertEquals('متوسط', $intermediate->getLabel());
        $this->assertEquals('نامي', $developing->getLabel());
        $this->assertEquals('مبتدئ', $beginner->getLabel());
    }

    public function testScoreComparison(): void
    {
        $high = new Score(80);
        $low = new Score(30);

        $this->assertTrue($high->isGreaterThan($low));
        $this->assertFalse($low->isGreaterThan($high));
    }
}
