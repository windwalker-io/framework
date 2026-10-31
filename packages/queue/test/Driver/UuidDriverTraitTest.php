<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Windwalker\Queue\Driver\UuidDriverTrait;
use Windwalker\Queue\Enum\DatabaseIdType;

class UuidDriverTraitTest extends TestCase
{
    private function createDriver(DatabaseIdType $type): object
    {
        $driver = new class () {
            use UuidDriverTrait;

            public function setIdType(DatabaseIdType $type): void
            {
                $this->idType = $type;
            }
        };
        $driver->setIdType($type);

        return $driver;
    }

    #[Test]
    public function generateUuidStringReturnsTextUuid(): void
    {
        $uuid = $this->createDriver(DatabaseIdType::UUID)->generateUuidString();

        self::assertTrue(Uuid::isValid($uuid));
        self::assertSame(36, strlen($uuid));
    }

    #[Test]
    public function generateUuidStringReturnsBinaryForUuidBin(): void
    {
        $bytes = $this->createDriver(DatabaseIdType::UUID_BIN)->generateUuidString();

        self::assertSame(16, strlen($bytes));
    }

    #[Test]
    public function generateUuidCreatesVersion7Uuid(): void
    {
        $uuid = $this->createDriver(DatabaseIdType::UUID)->generateUuid();

        self::assertSame(7, $uuid->getFields()->getVersion());
    }
}
