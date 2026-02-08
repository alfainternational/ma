<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User(
            id: 'test-uuid-123',
            email: 'test@example.com',
            passwordHash: 'hashed_password',
            fullName: 'Test User',
            role: 'user',
            status: 'active',
        );

        $this->assertEquals('test-uuid-123', $user->getId());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getFullName());
        $this->assertEquals('user', $user->getRole());
        $this->assertEquals('active', $user->getStatus());
    }

    public function testUserToArray(): void
    {
        $user = new User(
            id: 'test-uuid-123',
            email: 'test@example.com',
            passwordHash: 'hashed_password',
            fullName: 'Test User',
        );

        $array = $user->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('test-uuid-123', $array['id']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('Test User', $array['full_name']);
    }

    public function testUserFromArray(): void
    {
        $data = [
            'id' => 'test-uuid-456',
            'email' => 'another@example.com',
            'password_hash' => 'some_hash',
            'full_name' => 'Another User',
            'phone' => '+966501234567',
            'role' => 'admin',
            'status' => 'active',
        ];

        $user = User::fromArray($data);

        $this->assertEquals('test-uuid-456', $user->getId());
        $this->assertEquals('another@example.com', $user->getEmail());
        $this->assertEquals('Another User', $user->getFullName());
        $this->assertEquals('admin', $user->getRole());
    }
}
