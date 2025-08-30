<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\DI\Attributes\Lazy;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\Test\Mock\Bar;
use Windwalker\DI\Test\Mock\Foo;
use Windwalker\DI\Test\Mock\StubStack;

use Windwalker\DI\Test\Stub\StubLazy;

use Windwalker\DI\Test\Stub\StubLazyProperty;

use function Windwalker\object_is_proxy;
use function Windwalker\object_is_uninitialized_lazy;
use function Windwalker\unwrap_lazy_object;
use function Windwalker\unwrap_object_id;

class ContainerLazyTest extends TestCase
{
    public function testCreateLazyObject(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));

        $foo = $container->createObject(Foo::class);

        self::assertIsUninitializedLazyProxy($foo);
        self::assertIsUninitializedLazyProxy($foo->bar);
        self::assertIsUninitializedLazyProxy($foo->lazy);
        self::assertIsNotUninitializedLazyProxy($foo);
        self::assertIsNotUninitializedLazyProxy($foo->bar->queue);
        self::assertIsNotUninitializedLazyProxy($foo->bar->stack);

        // Bind a sub class
        $container->clear();

        // Test internal object should not lazy
        $stack = $container->newInstance(StubStack::class);

        self::assertIsNotUninitializedLazyProxy($stack);
    }

    public function testCreateObjectWithLazyDependency(): void
    {
        $container = new Container(options: new DIOptions(autowire: true));

        $foo = $container->newInstance(Foo::class);

        self::assertIsNotUninitializedLazyProxy($foo);
        self::assertIsUninitializedLazyProxy($foo->lazy);
        self::assertIsNotUninitializedLazyProxy($foo->bar);
    }

    public function testNewInstanceWithCallable(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        /** @var Foo $foo */
        $foo = $container->newInstance(fn(Bar $bar, StubLazy $lazy) => new Foo($bar, $lazy));

        self::assertInstanceOf(Bar::class, $foo->bar);
        self::assertIsUninitializedLazyProxy($foo->lazy);
    }

    public function testLazyParameters(): void
    {
        $container = new Container(options: new DIOptions(autowire: true));
        $container->registerDefaultAttributes();
        $paramsBar = null;

        $func = function (#[Lazy] Bar $bar) use (&$paramsBar) {
            $paramsBar = $bar;

            return $bar;
        };

        /** @var Bar $bar */
        $bar = $container->call($func);

        self::assertIsUninitializedLazyProxy($paramsBar, 'Params should be uninited lazy proxy.');
        self::assertIsUninitializedLazyProxy($bar, 'Return should be uninited lazy proxy.');
        self::assertIsLazyProxy($bar, 'Return should be lazy proxy no matter inited or not.');

        // Initialise proxy
        $stack = $bar->stack;

        self::assertIsNotUninitializedLazyProxy(
            $bar,
            'Params should be inited lazy proxy after access property.'
        );
        self::assertNotEquals(
            spl_object_id($bar),
            unwrap_object_id($bar),
            'The proxy object id should be different with unwrapped object id after init.'
        );
        self::assertIsLazyProxy($bar, 'Return should be lazy proxy no matter inited or not.');
        self::assertIsNotLazyProxy(unwrap_lazy_object($bar), 'Unwrapped object should not be lazy proxy.');
    }

    public function testLazyProperty(): void
    {
        $container = new Container(options: new DIOptions(autowire: true));
        $container->registerDefaultAttributes();

        $item = $container->newInstance(StubLazyProperty::class);

        self::assertIsUninitializedLazyProxy($item->lazy, 'Property should be uninited lazy proxy.');
        self::assertIsLazyProxy($item->lazy, 'Property should be lazy proxy no matter inited or not.');

        // Initialise proxy
        $stack = $item->lazy->stack;

        self::assertIsNotUninitializedLazyProxy(
            $item->lazy,
            'Property should be inited lazy proxy after access property.'
        );
        self::assertNotEquals(
            spl_object_id($item->lazy),
            unwrap_object_id($item->lazy),
            'The proxy object id should be different with unwrapped object id after init.'
        );
        self::assertIsLazyProxy($item->lazy, 'Property should be lazy proxy no matter inited or not.');
        self::assertIsNotLazyProxy(unwrap_lazy_object($item->lazy), 'Unwrapped object should not be lazy proxy.');
    }

    protected static function assertIsUninitializedLazyProxy(object $obj, string $message = ''): void
    {
        self::assertTrue(
            object_is_uninitialized_lazy($obj),
            $message ?: $obj::class . ' should be lazy proxy.'
        );
    }

    protected static function assertIsNotUninitializedLazyProxy(object $obj, string $message = ''): void
    {
        self::assertFalse(
            object_is_uninitialized_lazy($obj),
            $message ?: $obj::class . ' should not be lazy proxy.'
        );
    }

    protected static function assertIsLazyProxy(object $obj, string $message = ''): void
    {
        self::assertTrue(
            object_is_proxy($obj),
            $message ?: $obj::class . ' should not be lazy proxy.'
        );
    }

    protected static function assertIsNotLazyProxy(object $obj, string $message = ''): void
    {
        self::assertFalse(
            object_is_proxy($obj),
            $message ?: $obj::class . ' should be lazy proxy.'
        );
    }
}
