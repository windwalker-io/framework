<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Reflection;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use Windwalker\Utilities\Reflection\ReflectionCallable;

class ReflectionCallableTest extends TestCase
{
    #[Test]
    #[DataProvider('callableProvider')]
    public function constructorDetectsCallableType(mixed $callable, int $type): void
    {
        self::assertSame($type, new ReflectionCallable($callable)->getType());
    }

    public static function callableProvider(): array
    {
        return [
            'function name' => ['strtoupper', ReflectionCallable::TYPE_FUNCTION],
            'static method string' => [StubReflectionCallable::class . '::twice', ReflectionCallable::TYPE_STATIC_METHOD],
            'static method array' => [[StubReflectionCallable::class, 'twice'], ReflectionCallable::TYPE_STATIC_METHOD],
            'object method' => [[new StubReflectionCallable(), 'add'], ReflectionCallable::TYPE_OBJECT_METHOD],
            'closure' => [fn() => 1, ReflectionCallable::TYPE_CLOSURE],
            'invokable object' => [new StubReflectionCallable(), ReflectionCallable::TYPE_OBJECT_METHOD],
        ];
    }

    #[Test]
    public function constructorRejectsUnknownCallable(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('Unknown callable: integer(1)');

        new ReflectionCallable(1);
    }

    #[Test]
    #[DataProvider('closureProvider')]
    public function getClosureReturnsWorkingClosure(mixed $callable, array $args, mixed $expected): void
    {
        $closure = new ReflectionCallable($callable)->getClosure();

        self::assertInstanceOf(\Closure::class, $closure);
        self::assertSame($expected, $closure(...$args));
    }

    public static function closureProvider(): array
    {
        $closure = fn(int $a) => $a + 1;

        return [
            'function' => ['strtoupper', ['a'], 'A'],
            'static string' => [StubReflectionCallable::class . '::twice', [2], 4],
            'static array' => [[StubReflectionCallable::class, 'twice'], [3], 6],
            'object method' => [[new StubReflectionCallable(), 'add'], [1, 2], 3],
            'closure' => [$closure, [1], 2],
            'invokable' => [new StubReflectionCallable(), [], 'invoked'],
            'magic __call' => [[new StubReflectionCallable(), 'magic'], ['x'], 'magic:x'],
            'magic __callStatic' => [[StubReflectionCallable::class, 'staticMagic'], ['x'], 'static-magic:x'],
        ];
    }

    #[Test]
    public function getClosureReturnsSameClosureForClosureCallable(): void
    {
        $closure = fn() => 1;

        self::assertSame($closure, new ReflectionCallable($closure)->getClosure());
    }

    #[Test]
    public function getReflectorReturnsFunctionReflectionForFunctionsAndClosures(): void
    {
        self::assertSame('strtoupper', new ReflectionCallable('strtoupper')->getReflector()->getName());
        self::assertInstanceOf(ReflectionFunction::class, new ReflectionCallable(fn() => 1)->getReflector());
    }

    #[Test]
    public function getReflectorReturnsMethodReflectionForMethods(): void
    {
        $ref = new ReflectionCallable([new StubReflectionCallable(), 'add'])->getReflector();

        self::assertInstanceOf(ReflectionMethod::class, $ref);
        self::assertSame('add', $ref->getName());

        $ref = new ReflectionCallable(StubReflectionCallable::class . '::twice')->getReflector();

        self::assertInstanceOf(ReflectionMethod::class, $ref);
        self::assertSame('twice', $ref->getName());
    }

    #[Test]
    public function getReflectorWrapsMagicMethodsWithFunctionReflection(): void
    {
        $ref = new ReflectionCallable([new StubReflectionCallable(), 'magic'])->getReflector();

        self::assertInstanceOf(ReflectionFunction::class, $ref);
    }

    #[Test]
    public function toStringExportsReflector(): void
    {
        self::assertStringContainsString('Method [ ', (string) new ReflectionCallable([new StubReflectionCallable(), 'add']));
    }

    #[Test]
    public function exportReturnsNull(): void
    {
        self::assertNull(ReflectionCallable::export());
    }

    #[Test]
    public function getObjectOrClassReturnsInstanceOrClassName(): void
    {
        $object = new StubReflectionCallable();

        self::assertSame($object, new ReflectionCallable([$object, 'add'])->getObjectOrClass());
        self::assertSame(
            StubReflectionCallable::class,
            new ReflectionCallable([StubReflectionCallable::class, 'twice'])->getObjectOrClass()
        );
        self::assertNull(new ReflectionCallable('strtoupper')->getObjectOrClass());
    }
}

/**
 * Fixture offering static, instance, invokable and magic methods, used by ReflectionCallableTest.
 *
 * Remove it together with ReflectionCallableTest.
 */
class StubReflectionCallable
{
    public static function twice(int $n): int
    {
        return $n * 2;
    }

    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public function __invoke(): string
    {
        return 'invoked';
    }

    public function __call(string $name, array $args): string
    {
        return $name . ':' . $args[0];
    }

    public static function __callStatic(string $name, array $args): string
    {
        return 'static-magic:' . $args[0];
    }
}
