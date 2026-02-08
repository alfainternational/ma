<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Utils;

use App\Shared\Utils\UUID;
use PHPUnit\Framework\TestCase;

class UUIDTest extends TestCase
{
    public function testGenerateReturnsValidUUID(): void
    {
        $uuid = UUID::generate();
        $this->assertTrue(UUID::isValid($uuid));
    }

    public function testGenerateReturnsUniqueValues(): void
    {
        $uuid1 = UUID::generate();
        $uuid2 = UUID::generate();
        $this->assertNotEquals($uuid1, $uuid2);
    }

    public function testIsValidWithValidUUID(): void
    {
        $this->assertTrue(UUID::isValid('550e8400-e29b-41d4-a716-446655440000'));
    }

    public function testIsValidWithInvalidUUID(): void
    {
        $this->assertFalse(UUID::isValid('not-a-uuid'));
        $this->assertFalse(UUID::isValid(''));
        $this->assertFalse(UUID::isValid('12345'));
    }

    public function testToBinAndFromBin(): void
    {
        $uuid = UUID::generate();
        $bin = UUID::toBin($uuid);

        $this->assertEquals(16, strlen($bin));

        $restored = UUID::fromBin($bin);
        $this->assertEquals($uuid, $restored);
    }
}
