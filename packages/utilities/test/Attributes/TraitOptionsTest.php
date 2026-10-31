<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Attributes;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Attributes\TraitOptions;

class TraitOptionsTest extends TestCase
{
    #[Before]
    #[After]
    protected function resetCache(): void
    {
        TraitOptions::cacheReset();
    }

    #[Test]
    public function findAttributeReturnsAttributeInstanceOrNull(): void
    {
        self::assertSame(['Foo' => ['a' => 1]], TraitOptions::findAttribute(StubWithTraitOptions::class)->options);
        self::assertNull(TraitOptions::findAttribute(\stdClass::class));
    }

    #[Test]
    public function getAllOptionsAcceptsClassNameOrObject(): void
    {
        self::assertSame(['Foo' => ['a' => 1]], TraitOptions::getAllOptions(StubWithTraitOptions::class));
        self::assertSame(['Foo' => ['a' => 1]], TraitOptions::getAllOptions(new StubWithTraitOptions()));
        self::assertSame([], TraitOptions::getAllOptions(\stdClass::class));
    }

    #[Test]
    public function getOptionsReturnsOptionsOfTraitOrEmptyArray(): void
    {
        self::assertSame(['a' => 1], TraitOptions::getOptions(StubWithTraitOptions::class, 'Foo'));
        self::assertSame([], TraitOptions::getOptions(StubWithTraitOptions::class, 'Bar'));
    }

    #[Test]
    public function getOptionReturnsSingleValueOrNull(): void
    {
        self::assertSame(1, TraitOptions::getOption(StubWithTraitOptions::class, 'Foo', 'a'));
        self::assertNull(TraitOptions::getOption(StubWithTraitOptions::class, 'Foo', 'missing'));
        self::assertNull(TraitOptions::getOption(\stdClass::class, 'Foo', 'a'));
    }
}

/**
 * Fixture class carrying TraitOptions, used by TraitOptionsTest.
 *
 * Remove it together with TraitOptionsTest.
 */
#[TraitOptions(['Foo' => ['a' => 1]])]
class StubWithTraitOptions {}
