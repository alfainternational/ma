<?php
declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
    public function update(User $user): void;
    public function delete(string $id): void;
    public function findAll(int $limit = 50, int $offset = 0): array;
    public function countAll(): int;
}
