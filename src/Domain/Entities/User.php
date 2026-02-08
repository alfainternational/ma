<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Shared\Utils\UUID;
use InvalidArgumentException;

class User
{
    private const VALID_ROLES    = ['client', 'admin', 'analyst'];
    private const VALID_STATUSES = ['active', 'suspended', 'banned'];

    private string  $id;
    private string  $email;
    private string  $passwordHash;
    private string  $fullName;
    private ?string $phone;
    private string  $role;
    private string  $status;
    private string  $createdAt;
    private string  $updatedAt;

    public function __construct(
        string  $id,
        string  $email,
        string  $passwordHash,
        string  $fullName,
        ?string $phone,
        string  $role,
        string  $status = 'active',
        ?string $createdAt = null,
        ?string $updatedAt = null,
    ) {
        if (!in_array($role, self::VALID_ROLES, true)) {
            throw new InvalidArgumentException("Invalid role: {$role}");
        }
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }

        $this->id           = $id;
        $this->email        = $email;
        $this->passwordHash = $passwordHash;
        $this->fullName     = $fullName;
        $this->phone        = $phone;
        $this->role         = $role;
        $this->status       = $status;
        $this->createdAt    = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt    = $updatedAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): string           { return $this->id; }
    public function getEmail(): string        { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getFullName(): string     { return $this->fullName; }
    public function getPhone(): ?string       { return $this->phone; }
    public function getRole(): string         { return $this->role; }
    public function getStatus(): string       { return $this->status; }
    public function getCreatedAt(): string    { return $this->createdAt; }
    public function getUpdatedAt(): string    { return $this->updatedAt; }

    public function setEmail(string $email): void           { $this->email = $email; $this->touch(); }
    public function setPasswordHash(string $hash): void     { $this->passwordHash = $hash; $this->touch(); }
    public function setFullName(string $fullName): void     { $this->fullName = $fullName; $this->touch(); }
    public function setPhone(?string $phone): void          { $this->phone = $phone; $this->touch(); }
    public function setRole(string $role): void
    {
        if (!in_array($role, self::VALID_ROLES, true)) {
            throw new InvalidArgumentException("Invalid role: {$role}");
        }
        $this->role = $role;
        $this->touch();
    }
    public function setStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }
        $this->status = $status;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'email'         => $this->email,
            'password_hash' => $this->passwordHash,
            'full_name'     => $this->fullName,
            'phone'         => $this->phone,
            'role'          => $this->role,
            'status'        => $this->status,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id:           $data['id'],
            email:        $data['email'],
            passwordHash: $data['password_hash'],
            fullName:     $data['full_name'],
            phone:        $data['phone'] ?? null,
            role:         $data['role'],
            status:       $data['status'] ?? 'active',
            createdAt:    $data['created_at'] ?? null,
            updatedAt:    $data['updated_at'] ?? null,
        );
    }
}
