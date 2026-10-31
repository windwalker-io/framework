<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Windwalker\object_is_proxy;
use function Windwalker\object_is_uninitialized_lazy;
use function Windwalker\unwrap_lazy_object;
use function Windwalker\unwrap_object_hash;
use function Windwalker\unwrap_object_id;

class FunctionsLazyTest extends TestCase
{
    private function createLazy(\Closure $factory): LazySubject
    {
        return new \ReflectionClass(LazySubject::class)->newLazyProxy($factory);
    }

    #[Test]
    public function lazyProxyIsUninitializedUntilUnwrapped(): void
    {
        $real = new LazySubject();
        $lazy = $this->createLazy(fn() => $real);

        self::assertTrue(object_is_uninitialized_lazy($lazy));
        self::assertTrue(object_is_proxy($lazy));

        self::assertSame($real, unwrap_lazy_object($lazy));
        self::assertFalse(object_is_uninitialized_lazy($lazy));
        self::assertTrue(object_is_proxy($lazy));
    }

    #[Test]
    public function unwrapObjectIdAndHashReturnRealInstanceIdentity(): void
    {
        $real = new LazySubject();
        $lazy = $this->createLazy(fn() => $real);

        self::assertSame(spl_object_id($real), unwrap_object_id($lazy));
        self::assertSame(spl_object_hash($real), unwrap_object_hash($lazy));
    }

    #[Test]
    public function lazyGhostIsUnwrappedToItself(): void
    {
        $ghost = new \ReflectionClass(LazySubject::class)->newLazyGhost(static function (LazySubject $o): void {
            $o->value = 'ready';
        });

        self::assertTrue(object_is_uninitialized_lazy($ghost));
        self::assertSame($ghost, unwrap_lazy_object($ghost));
        self::assertSame('ready', $ghost->value);
        self::assertFalse(object_is_proxy($ghost));
    }

    #[Test]
    public function plainObjectIsNotProxy(): void
    {
        $obj = new LazySubject();

        self::assertFalse(object_is_proxy($obj));
        self::assertFalse(object_is_uninitialized_lazy($obj));
        self::assertSame($obj, unwrap_lazy_object($obj));
    }
}

/**
 * Plain user-land class used as a lazy object target, since internal classes such as stdClass or ArrayObject
 * cannot be made lazy reliably. Removable together with FunctionsLazyTest.
 */
class LazySubject
{
    public string $value = '';
}
