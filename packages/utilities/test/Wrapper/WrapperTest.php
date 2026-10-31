<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Wrapper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Collection;
use Windwalker\Utilities\Wrapper\DepthWrapper;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\ValueReference;

class WrapperTest extends TestCase
{
    /**
     * @see  RawWrapper::get
     * @see  RawWrapper::set
     * @see  RawWrapper::__invoke
     */
    #[Test]
    public function rawWrapperHoldsAndReplacesValue(): void
    {
        $wrapper = new RawWrapper('foo');

        self::assertSame('foo', $wrapper->get());
        self::assertSame('foo', $wrapper('ignored'));
        self::assertSame($wrapper, $wrapper->set(123));
        self::assertSame(123, $wrapper());
    }

    /**
     * @see  RawWrapper::__toString
     */
    #[Test]
    public function rawWrapperCastsValueToString(): void
    {
        self::assertSame('12', (string) new RawWrapper(12));
    }

    /**
     * @see  DepthWrapper::__invoke
     */
    #[Test]
    public function depthWrapperReturnsConfiguredDepth(): void
    {
        self::assertSame(5, (new DepthWrapper())(null));
        self::assertSame(2, (new DepthWrapper(2))(['x']));
    }

    /**
     * @see  ValueReference::__invoke
     */
    #[Test]
    public function valueReferenceReadsPathFromArray(): void
    {
        $ref = new ValueReference('a.b');

        self::assertSame(1, $ref(['a' => ['b' => 1]]));
        self::assertNull($ref(['a' => []]));
    }

    /**
     * @see  ValueReference::__invoke
     */
    #[Test]
    public function valueReferenceUsesOverrideDelimiterWhenGiven(): void
    {
        $ref = new ValueReference('a.b', '.');

        self::assertSame(1, $ref(['a' => ['b' => 1]], '.'));
        self::assertSame(2, (new ValueReference('a/b', '/'))(['a' => ['b' => 2]]));
        self::assertSame(3, (new ValueReference('a/b'))(['a' => ['b' => 3]], '/'));
    }

    /**
     * @see  ValueReference::__invoke
     */
    #[Test]
    public function valueReferenceReadsPathFromCollection(): void
    {
        $ref = new ValueReference('a.b');

        self::assertSame(1, $ref(new Collection(['a' => ['b' => 1]])));
    }
}
