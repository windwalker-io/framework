<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\ORMOptions;

/**
 * The ORMOptionsTest class.
 */
class ORMOptionsTest extends TestCase
{
    #[Test]
    public function wrapConvertsLegacyBitmaskToOptions(): void
    {
        $options = ORMOptions::wrap(
            EntityMapper::UPDATE_NULLS | EntityMapper::IGNORE_EVENTS | EntityMapper::TRANSACTION
        );

        self::assertTrue($options->updateNulls);
        self::assertTrue($options->ignoreEvents);
        self::assertTrue($options->transaction);
        self::assertFalse($options->ignoreOldData);
        self::assertFalse($options->forUpdate);
    }

    #[Test]
    public function wrapConvertsLegacyForUpdateFlag(): void
    {
        self::assertTrue(ORMOptions::wrap(EntityMapper::FOR_UPDATE)->forUpdate);
    }

    #[Test]
    public function wrapReturnsSameInstanceWhenAlreadyOptions(): void
    {
        $options = new ORMOptions(updateNulls: true);

        self::assertSame($options, ORMOptions::wrap($options));
    }

    #[Test]
    public function wrapMapsArrayKeysToConstructorArguments(): void
    {
        $options = ORMOptions::wrap(['updateNulls' => true, 'optimisticLock' => false]);

        self::assertTrue($options->updateNulls);
        self::assertFalse($options->optimisticLock);
    }

    #[Test]
    public function wrapTreatsNullAsDefaults(): void
    {
        $options = ORMOptions::wrap(null);

        self::assertFalse($options->updateNulls);
        self::assertTrue($options->optimisticLock);
    }

    #[Test]
    public function lockDoHooksNormalizeLockModes(): void
    {
        $options = new ORMOptions(forUpdate: 'skip locked', forShare: ORMOptions::NOWAIT);

        self::assertSame(ORMOptions::SKIP_LOCKED, $options->forUpdateDo);
        self::assertSame(ORMOptions::NOWAIT, $options->forShareDo);
    }

    #[Test]
    public function lockDoHooksReturnNullForBooleanFlags(): void
    {
        $options = new ORMOptions(forUpdate: true, forShare: false);

        self::assertNull($options->forUpdateDo);
        self::assertNull($options->forShareDo);
    }
}
