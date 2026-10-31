<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Windwalker\Queue\Enum\DatabaseIdType;

class DatabaseIdTypeTest extends TestCase
{
    private const string UUID_STRING = '3f2504e0-4f89-11d3-9a0c-0305e82c3301';

    #[Test]
    #[DataProvider('typeFlagsProvider')]
    public function typeFlagsMatchIdKind(DatabaseIdType $type, bool $isUuid, bool $isInt): void
    {
        self::assertSame($isUuid, $type->isUuid());
        self::assertSame($isInt, $type->isInt());
        self::assertSame($isInt, $type->shouldAutoIncrement());
    }

    public static function typeFlagsProvider(): array
    {
        return [
            'int' => [DatabaseIdType::INT, false, true],
            'uuid' => [DatabaseIdType::UUID, true, false],
            'uuid bin' => [DatabaseIdType::UUID_BIN, true, false],
        ];
    }

    #[Test]
    public function toWritableKeepsNumericIdForInt(): void
    {
        self::assertSame(5, DatabaseIdType::INT->toWritable(5));
        self::assertSame('5', DatabaseIdType::INT->toWritable('5'));
    }

    #[Test]
    public function toWritableRejectsNonNumericIdForInt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('ID must be integer');

        DatabaseIdType::INT->toWritable('abc');
    }

    #[Test]
    public function toWritableConvertsToUuidStringForUuid(): void
    {
        $uuid = Uuid::fromString(self::UUID_STRING);

        self::assertSame(self::UUID_STRING, DatabaseIdType::UUID->toWritable($uuid));
        self::assertSame(self::UUID_STRING, DatabaseIdType::UUID->toWritable($uuid->getBytes()));
        self::assertSame(self::UUID_STRING, DatabaseIdType::UUID->toWritable(self::UUID_STRING));
    }

    #[Test]
    public function toWritableConvertsToBinaryForUuidBin(): void
    {
        $uuid = Uuid::fromString(self::UUID_STRING);

        self::assertSame($uuid->getBytes(), DatabaseIdType::UUID_BIN->toWritable($uuid));
        self::assertSame($uuid->getBytes(), DatabaseIdType::UUID_BIN->toWritable($uuid->getBytes()));
        self::assertSame($uuid->getBytes(), DatabaseIdType::UUID_BIN->toWritable(self::UUID_STRING));
    }

    #[Test]
    #[DataProvider('uuidTypesProvider')]
    public function toWritableRejectsInvalidUuidString(DatabaseIdType $type): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid UUID string');

        $type->toWritable('not-a-uuid');
    }

    public static function uuidTypesProvider(): array
    {
        return [
            'uuid' => [DatabaseIdType::UUID],
            'uuid bin' => [DatabaseIdType::UUID_BIN],
        ];
    }

    #[Test]
    public function toReadableCastsIntIdToString(): void
    {
        self::assertSame('12', DatabaseIdType::INT->toReadable(12));
    }

    #[Test]
    public function toReadableConvertsUuidBytesToString(): void
    {
        $bytes = Uuid::fromString(self::UUID_STRING)->getBytes();

        self::assertSame(self::UUID_STRING, DatabaseIdType::UUID_BIN->toReadable($bytes));
        self::assertSame(self::UUID_STRING, DatabaseIdType::UUID->toReadable(self::UUID_STRING));
    }
}
