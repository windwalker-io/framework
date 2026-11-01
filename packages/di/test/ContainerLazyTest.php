<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DI\Attributes\Lazy;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\Test\Mock\Bar;
use Windwalker\DI\Test\Mock\Foo;
use Windwalker\DI\Test\Mock\StubStack;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Test\Stub\StubLazy;
use Windwalker\DI\Test\Stub\StubLazyConsumer;
use Windwalker\DI\Test\Stub\StubLazyContract;
use Windwalker\DI\Test\Stub\StubLazyContractImpl;
use Windwalker\DI\Test\Stub\StubLazyCounter;
use Windwalker\DI\Test\Stub\StubLazyCounterConsumer;
use Windwalker\DI\Test\Stub\StubLazyCounterExtra;
use Windwalker\DI\Test\Stub\StubLazyFinal;
use Windwalker\DI\Test\Stub\StubLazyThrows;
use Windwalker\DI\Test\Stub\StubLazyProperty;

use function Windwalker\object_is_proxy;
use function Windwalker\object_is_uninitialized_lazy;
use function Windwalker\unwrap_lazy_object;
use function Windwalker\unwrap_object_id;

class ContainerLazyTest extends TestCase
{
    #[Test]
    public function createsLazyObjectsAndEagerInternalDependencies(): void
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

    #[Test]
    public function createsAnEagerObjectWithLazyDependencies(): void
    {
        $container = new Container(options: new DIOptions(autowire: true));

        $foo = $container->newInstance(Foo::class);

        self::assertIsNotUninitializedLazyProxy($foo);
        self::assertIsUninitializedLazyProxy($foo->lazy);
        self::assertIsNotUninitializedLazyProxy($foo->bar);
    }

    #[Test]
    public function createsObjectsFromCallablesWithLazyDependencies(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        /** @var Foo $foo */
        $foo = $container->newInstance(fn(Bar $bar, StubLazy $lazy) => new Foo($bar, $lazy));

        self::assertInstanceOf(Bar::class, $foo->bar);
        self::assertIsUninitializedLazyProxy($foo->lazy);
    }

    #[Test]
    public function injectsLazyParametersAndInitializesOnAccess(): void
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

    #[Test]
    public function injectsLazyPropertiesAndInitializesOnAccess(): void
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

    #[Before]
    public function resetCounter(): void
    {
        StubLazyCounter::$created = 0;
    }

    #[Test]
    public function doesNotCreateObjectUntilAccessed(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));

        $obj = $container->newInstance(StubLazyCounter::class);

        self::assertSame(0, StubLazyCounter::$created);
        self::assertIsUninitializedLazyProxy($obj);

        self::assertSame('hello origin', $obj->hello());
        self::assertSame('hello origin', $obj->hello());
        self::assertSame(1, StubLazyCounter::$created);
    }

    #[Test]
    public function sharedLazyServiceKeepsSingleIdentityAndInstance(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));
        $container->share(StubLazyCounter::class, fn(Container $c) => $c->newInstance(StubLazyCounter::class));

        $a = $container->get(StubLazyCounter::class);
        $b = $container->get(StubLazyCounter::class);

        self::assertSame($a, $b);

        $a->hello();

        self::assertSame(1, StubLazyCounter::$created);
    }

    #[Test]
    public function appliesExtendsToLazyObjects(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));
        $container->whenCreating(StubLazyCounter::class)
            ->extend(function (StubLazyCounter $obj) {
                $obj->name = 'extended';

                return $obj;
            });

        $obj = $container->newInstance(StubLazyCounter::class);

        self::assertSame('extended', $obj->name);
    }

    #[Test]
    public function fallsBackToEagerForFinalClassUnderGlobalLazy(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));

        $obj = $container->newInstance(StubLazyFinal::class);

        self::assertSame('final', $obj->hello());
    }

    #[Test]
    public function nonInstantiableFailsLikeEagerUnderGlobalLazy(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));

        // Same behavior as eager creation: lazy must not hide or replace the error.
        $this->expectException(\Error::class);

        $container->newInstance(\Countable::class);
    }

    #[Test]
    public function missingClassFailsLikeEagerUnderGlobalLazy(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));

        $this->expectException(\ReflectionException::class);

        $container->newInstance('Windwalker\\DI\\Test\\Stub\\NotExistsClass');
    }

    #[Test]
    public function wrapsDeferredConstructorErrorsInResolveException(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));

        $obj = $container->newInstance(StubLazyThrows::class);

        $this->expectException(DefinitionResolveException::class);
        $this->expectExceptionMessage(StubLazyThrows::class);

        $obj->name;
    }

    #[Test]
    public function initializedInstanceIsNotAnotherLazyProxy(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));

        $obj = $container->newInstance(StubLazyCounter::class);
        $obj->hello();

        self::assertIsNotLazyProxy(unwrap_lazy_object($obj));
    }

    #[Test]
    public function injectsInterfaceDependencyUnderGlobalLazy(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));
        $container->bind(StubLazyContract::class, StubLazyContractImpl::class);

        $consumer = $container->newInstance(StubLazyConsumer::class);

        self::assertSame(0, StubLazyCounter::$created);
        self::assertSame('impl', $consumer->contract->name());
        self::assertInstanceOf(StubLazyContract::class, $consumer->contract);
    }

    #[Test]
    public function injectsConcreteDependencyLazilyAndKeepsOptionalNull(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));
        $container->bind(StubLazyContract::class, StubLazyContractImpl::class);

        $consumer = $container->newInstance(StubLazyConsumer::class);

        self::assertSame(0, StubLazyCounter::$created);
        self::assertSame('hello origin', $consumer->counter->hello());
        self::assertSame(1, StubLazyCounter::$created);
        self::assertNull($consumer->optional);
    }

    #[Test]
    public function throwsExplainableExceptionWhenBoundSubclassIsIncompatibleWithProxy(): void
    {
        $container = new Container(options: new DIOptions(autowire: true, lazy: true));
        $container->bind(StubLazyCounter::class, StubLazyCounterExtra::class);

        $consumer = $container->newInstance(StubLazyCounterConsumer::class);

        try {
            $consumer->counter->name;
            self::fail('Should throw');
        } catch (DefinitionResolveException $e) {
            self::assertStringContainsString('Unable to initialize lazy object', $e->getMessage());
            self::assertStringContainsString(StubLazyCounterExtra::class, $e->getMessage());
            self::assertStringContainsString('lazy: false', $e->getMessage());
        }
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
