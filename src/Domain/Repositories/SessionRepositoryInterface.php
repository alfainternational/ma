<?php
declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\AssessmentSession;

interface SessionRepositoryInterface
{
    public function findById(string $id): ?AssessmentSession;
    public function findByCompanyId(string $companyId, int $limit = 50, int $offset = 0): array;
    public function findByUserId(string $userId): array;
    public function save(AssessmentSession $session): void;
    public function update(AssessmentSession $session): void;
    public function delete(string $id): void;
    public function findActiveByCompanyId(string $companyId): ?AssessmentSession;
}
