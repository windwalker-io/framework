<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use Windwalker\ORM\ORMOptions;

use function Windwalker\entity_table;
use function Windwalker\try_big_decimal;
use function Windwalker\try_big_integer;
use function Windwalker\try_uuid;
use function Windwalker\try_wrap_uuid;

/**
 * The FunctionsTest class.
 */
class FunctionsTest extends TestCase
{
    #[Test]
    public function entityTableReturnsPlainTableNameAsIs(): void
    {
        self::assertSame('articles', entity_table('articles'));
    }

    #[Test]
    public function entityTableThrowsWhenClassHasNoTableAttribute(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIsOrContains('has no table info');

        entity_table(ORMOptions::class);
    }

    #[Test]
    public function tryBigDecimalKeepsNull(): void
    {
        self::assertNull(try_big_decimal(null));
    }

    #[Test]
    public function tryBigDecimalReturnsGivenInstance(): void
    {
        $decimal = BigDecimal::of('1.5');

        self::assertSame($decimal, try_big_decimal($decimal));
    }

    #[Test]
    public function tryBigDecimalConvertsFloatViaStringWithScale(): void
    {
        self::assertSame('0.1000', (string) try_big_decimal(0.1));
        self::assertSame('1.50', (string) try_big_decimal('1.5', 2));
    }

    #[Test]
    public function tryBigIntegerKeepsNull(): void
    {
        self::assertNull(try_big_integer(null));
    }

    #[Test]
    public function tryBigIntegerReturnsGivenInstance(): void
    {
        $int = BigInteger::of(5);

        self::assertSame($int, try_big_integer($int));
    }

    #[Test]
    public function tryBigIntegerConvertsScalarValues(): void
    {
        self::assertSame('123', (string) try_big_integer('123'));
        self::assertSame('42', (string) try_big_integer(42));
    }

    #[Test]
    public function tryWrapUuidKeepsNull(): void
    {
        self::assertNull(try_wrap_uuid(null));
    }

    #[Test]
    public function tryUuidKeepsNullAndEmptyBinaryMarker(): void
    {
        if (!interface_exists(\Ramsey\Uuid\UuidInterface::class)) {
            self::markTestSkipped('ramsey/uuid is not installed.');
        }

        self::assertNull(try_uuid(null));
        self::assertNull(try_uuid('0x'));
    }
}
