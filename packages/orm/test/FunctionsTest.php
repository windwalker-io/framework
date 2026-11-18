<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use Windwalker\ORM\ORMOptions;

use function Windwalker\entity_table;
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
