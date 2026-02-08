<?php
declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Question;

interface QuestionRepositoryInterface
{
    public function findById(string $id): ?Question;
    public function findByCategory(string $category): array;
    public function findBySector(string $sector): array;
    public function findAll(int $limit = 250, int $offset = 0): array;
    public function findNextQuestion(string $sessionId, ?string $currentQuestionId = null): ?Question;
    public function countByCategory(string $category): int;
    public function countAll(): int;
}
