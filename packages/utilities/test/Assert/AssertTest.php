<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Assert;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Assert\Assert;
use Windwalker\Utilities\Assert\LogicAssert;
use Windwalker\Utilities\Assert\RuntimeAssert;
use Windwalker\Utilities\Assert\TypeAssert;

/**
 * Subclass fixture. Can be removed with the inheritance test of Assert::create().
 */
class StubCustomAssert extends Assert
{
    public static function make(): static
    {
        return static::create();
    }
}

class AssertTest extends TestCase
{
    #[Test]
    public function createInSubclassUsesCallerOfSubclassMethod(): void
    {
        $assert = StubCustomAssert::make();

        self::assertInstanceOf(StubCustomAssert::class, $assert);
        self::assertSame(StubCustomAssert::class . '::make()', $assert->caller);
    }

    #[Test]
    public function createBuildsAssertThatThrowsGivenExceptionClassAndCode(): void
    {
        $assert = Assert::create(InvalidArgumentException::class, 422, 'Foo::bar()');

        try {
            $assert->throwException('Failed in {caller}');
            self::fail('Exception not thrown');
        } catch (InvalidArgumentException $e) {
            self::assertSame('Failed in Foo::bar()', $e->getMessage());
            self::assertSame(422, $e->getCode());
        }
    }

    #[Test]
    public function createUsesRuntimeExceptionAndCallerOfCreationByDefault(): void
    {
        $assert = Assert::create();

        self::assertSame(self::class . '::createUsesRuntimeExceptionAndCallerOfCreationByDefault()', $assert->caller);

        try {
            $assert->throwException('x');
            self::fail('Exception not thrown');
        } catch (RuntimeException $e) {
            self::assertSame(500, $e->getCode());
        }
    }

    #[Test]
    public function assertReturnsOriginalAssertionWhenPassed(): void
    {
        $assert = Assert::create();
        $callable = fn() => true;

        self::assertSame($callable, $assert->assert($callable, 'fail'));
        self::assertTrue($assert->assert(true, 'fail'));
        self::assertSame('foo', $assert->assert('foo', 'fail'));
    }

    #[Test]
    #[DataProvider('failedAssertionProvider')]
    public function assertThrowsWhenAssertionIsFalsy(mixed $assertion): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('fail');

        Assert::create()->assert($assertion, 'fail');
    }

    public static function failedAssertionProvider(): array
    {
        return [
            'false' => [false],
            'null' => [null],
            'empty string' => [''],
            'closure returns false' => [fn() => false],
        ];
    }

    #[Test]
    public function invokeProxiesToAssert(): void
    {
        $assert = Assert::create();

        self::assertSame(1, $assert(1, 'fail'));

        $this->expectException(RuntimeException::class);

        $assert(0, 'fail');
    }

    #[Test]
    public function exceptionReturnsExceptionWithoutThrowing(): void
    {
        $assert = Assert::create(LogicException::class, 1, 'A::b()');

        $e = $assert->exception('Hello {caller} {value} {type}', 'str');

        self::assertInstanceOf(LogicException::class, $e);
        self::assertSame('Hello A::b() str string(3) "str"', $e->getMessage());
    }

    #[Test]
    public function messageClosureReceivesCallerValueAndDescription(): void
    {
        $assert = Assert::create(RuntimeException::class, 500, 'A::b()');

        $e = $assert->exception(fn(string $caller, mixed $value, string $type) => "$caller|$value|$type", 12);

        self::assertSame('A::b()|12|integer(12)', $e->getMessage());
    }

    #[Test]
    public function messageClosureErrorIsReportedInMessage(): void
    {
        $assert = Assert::create();

        $e = $assert->exception(fn() => throw new \Exception('boom'));

        self::assertSame('Error when generating assert message: boom', $e->getMessage());
    }

    #[Test]
    public function templateAccessesNestedValueByDotPath(): void
    {
        $e = Assert::create(RuntimeException::class, 500, 'A::b()')
            ->exception('Name is {value.name}', ['name' => 'Sakura']);

        self::assertSame('Name is Sakura', $e->getMessage());
    }

    #[Test]
    public function getCallerReturnsClassAndFunction(): void
    {
        self::assertSame(
            self::class . '::getCallerReturnsClassAndFunction()',
            self::callerOfCaller()
        );
    }

    private static function callerOfCaller(): string
    {
        return Assert::getCaller(2);
    }

    #[Test]
    #[DataProvider('describeValueProvider')]
    public function describeValueDescribesTypeOfValue(mixed $value, string $expected): void
    {
        self::assertSame($expected, Assert::describeValue($value));
    }

    public static function describeValueProvider(): array
    {
        return [
            'null' => [null, '(NULL)'],
            'true' => [true, 'BOOL (TRUE)'],
            'false' => [false, 'BOOL (FALSE)'],
            'object' => [new \stdClass(), \stdClass::class],
            'array' => [[1], 'array'],
            'string' => ['abc', 'string(3) "abc"'],
            'long string is truncated' => [
                str_repeat('a', 60),
                'string(53) "' . str_repeat('a', 50) . '..."',
            ],
            'int' => [5, 'integer(5)'],
            'float' => [1.5, 'double(1.5)'],
            'resource' => [STDIN, 'resource (stream)'],
        ];
    }

    #[Test]
    public function describeValueDescribesClosureWithScopeAndLines(): void
    {
        $closure = function () {};

        self::assertMatchesRegularExpression(
            '/^' . preg_quote(self::class, '/') . '::\{Closure\} \(\d+:\d+\)$/',
            Assert::describeValue($closure)
        );
    }

    #[Test]
    #[DataProvider('typedAssertProvider')]
    public function typedAssertsThrowTheirOwnExceptionType(string $assertClass, string $exceptionClass): void
    {
        try {
            $assertClass::assert(false, 'Bad {caller}');
            self::fail('Exception not thrown');
        } catch (\Throwable $e) {
            self::assertInstanceOf($exceptionClass, $e);
            self::assertSame(
                'Bad ' . self::class . '::typedAssertsThrowTheirOwnExceptionType()',
                $e->getMessage()
            );
        }

        self::assertSame('x', $assertClass::assert('x', 'Bad'));
    }

    public static function typedAssertProvider(): array
    {
        return [
            'type' => [TypeAssert::class, TypeError::class],
            'arguments' => [ArgumentsAssert::class, InvalidArgumentException::class],
            'logic' => [LogicAssert::class, LogicException::class],
            'runtime' => [RuntimeAssert::class, RuntimeException::class],
        ];
    }

    #[Test]
    public function typeAssertUsesCustomExceptionHandler(): void
    {
        $this->expectException(\DomainException::class);

        TypeAssert::assert(fn() => false, 'x', null, fn($msg) => new \DomainException($msg));
    }

    #[Test]
    public function typeAssertCreateAssertAndDescribeValue(): void
    {
        $assert = TypeAssert::createAssert(null, 'X::y()');

        self::assertSame('X::y()', $assert->caller);
        self::assertInstanceOf(TypeError::class, $assert->exception('m'));
        self::assertSame('integer(1)', TypeAssert::describeValue(1));
    }
}
