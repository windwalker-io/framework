<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Wrapper;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Windwalker\Query\Wrapper\UuidBinWrapper;
use Windwalker\Query\Wrapper\UuidWrapper;

class UuidWrapperTest extends TestCase
{
    private const UUID = '3a9b1c7e-5d2f-4e8a-9b1c-7e5d2f4e8a9b';

    private function requireUuidPackage(): void
    {
        if (!class_exists(Uuid::class)) {
            self::markTestSkipped('ramsey/uuid is not installed.');
        }
    }

    #[Test]
    public function constructorAcceptsStringAndInvokeReturnsUuidString(): void
    {
        $this->requireUuidPackage();

        $wrapper = new UuidWrapper(self::UUID);

        self::assertSame(self::UUID, $wrapper());
        self::assertSame(self::UUID, (string) $wrapper);
    }

    #[Test]
    public function constructorAcceptsBinaryString(): void
    {
        $this->requireUuidPackage();

        $wrapper = new UuidWrapper(Uuid::fromString(self::UUID)->getBytes());

        self::assertSame(self::UUID, (string) $wrapper);
    }

    #[Test]
    public function constructorAcceptsAnotherWrapper(): void
    {
        $this->requireUuidPackage();

        $wrapper = new UuidWrapper(new UuidWrapper(self::UUID));

        self::assertSame(self::UUID, (string) $wrapper);
    }

    #[Test]
    public function constructorAcceptsUuidInstance(): void
    {
        $this->requireUuidPackage();

        $uuid = Uuid::fromString(self::UUID);

        self::assertSame($uuid, (new UuidWrapper($uuid))->getRaw());
    }

    #[Test]
    public function binWrapperReturnsBytes(): void
    {
        $this->requireUuidPackage();

        $wrapper = new UuidBinWrapper(self::UUID);

        self::assertSame(Uuid::fromString(self::UUID)->getBytes(), $wrapper());
        self::assertSame(Uuid::fromString(self::UUID)->getBytes(), (string) $wrapper);
    }

    #[Test]
    #[DataProvider('emptyValuesProvider')]
    public function tryWrapReturnsNullForEmptyValues(?string $value): void
    {
        self::assertNull(UuidWrapper::tryWrap($value));
    }

    public static function emptyValuesProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'zero hex prefix' => ['0x'],
        ];
    }

    #[Test]
    public function tryWrapReturnsSameUuidInstance(): void
    {
        $this->requireUuidPackage();

        $uuid = Uuid::fromString(self::UUID);

        self::assertSame($uuid, UuidWrapper::tryWrap($uuid));
    }

    #[Test]
    public function wrapThrowsOnInvalidUuid(): void
    {
        $this->requireUuidPackage();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Invalid UUID string or UUID bytes.');

        UuidWrapper::wrap('not-a-uuid');
    }
}
