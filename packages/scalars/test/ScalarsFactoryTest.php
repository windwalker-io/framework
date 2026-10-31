<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Scalars\ScalarsFactory;
use Windwalker\Scalars\StringObject;

/**
 * Tests ScalarsFactory.
 */
class ScalarsFactoryTest extends TestCase
{
    #[Test]
    public function fromNativeWrapsArrayIntoArrayObject(): void
    {
        $r = ScalarsFactory::fromNative([1, 2]);

        self::assertInstanceOf(ArrayObject::class, $r);
        self::assertEquals([1, 2], $r->dump());
    }

    #[Test]
    public function fromNativeWrapsScalarIntoStringObject(): void
    {
        $r = ScalarsFactory::fromNative('foo');

        self::assertInstanceOf(StringObject::class, $r);
        self::assertEquals('foo', (string) $r);
    }

    #[Test]
    public function fromNativeReturnsOtherValuesUntouched(): void
    {
        $o = new \stdClass();

        self::assertSame($o, ScalarsFactory::fromNative($o));
        self::assertNull(ScalarsFactory::fromNative(null));
    }
}
