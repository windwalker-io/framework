<?php

declare(strict_types=1);

namespace Windwalker\Attributes\Test;

use Attribute;
use LogicException;
use ReflectionFunction;
use PHPUnit\Framework\TestCase;
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;
use Windwalker\Attributes\Test\Stub\Attrs\PropWrap;
use Windwalker\Attributes\Test\Stub\Attrs\StrUpper;
use Windwalker\Attributes\Test\Stub\Attrs\StubMemberResolver;
use Windwalker\Attributes\Test\Stub\Attrs\StubNotInvokable;
use Windwalker\Attributes\Test\Stub\Attrs\StubWrapper;
use Windwalker\Attributes\Test\Stub\Attrs\ValueImplode;
use Windwalker\Attributes\Test\Stub\StubAccessible;
use Windwalker\Attributes\Test\Stub\StubObject;
use Windwalker\Scalars\StringObject;

use function Windwalker\str;

use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The AttributeResolverTest class.
 */
class AttributesResolverTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?AttributesResolver $instance = null;

    public function testRegisterAttribute(): void
    {
        // Test property type
        $this->instance->registerAttribute(StubWrapper::class, Attribute::TARGET_PROPERTY);

        self::assertTrue(
            $this->instance->hasAttribute(StubWrapper::class)
        );

        self::assertFalse(
            $this->instance->hasAttribute(StubWrapper::class, Attribute::TARGET_CLASS)
        );

        // Test add class type
        $this->instance->registerAttribute(StubWrapper::class, Attribute::TARGET_CLASS);

        self::assertTrue(
            $this->instance->hasAttribute(StubWrapper::class, Attribute::TARGET_CLASS)
        );

        // Test remove property type
        $this->instance->removeAttribute(StubWrapper::class, Attribute::TARGET_PROPERTY);

        self::assertFalse(
            $this->instance->hasAttribute(StubWrapper::class, Attribute::TARGET_PROPERTY)
        );

        self::assertTrue(
            $this->instance->hasAttribute(StubWrapper::class, Attribute::TARGET_CLASS)
        );

        // Test remove all
        $this->instance->removeAttribute(StubWrapper::class, AttributeType::ALL);

        self::assertFalse(
            $this->instance->hasAttribute(StubWrapper::class)
        );
    }

    public function testRegisterAttributeCallable(): void
    {
        // Test property type
        $this->instance->registerAttribute(StrUpper::class, AttributeType::CALLABLE);

        $foo = new class {
            #[StrUpper]
            public function foo(): string
            {
                return 'foo';
            }
        };

        $r = $this->instance->call([$foo, 'foo']);

        self::assertEquals(
            'FOO',
            $r
        );
    }

    public function testClassCreate()
    {
        $this->instance->registerAttribute(StubWrapper::class, Attribute::TARGET_CLASS);

        $obj = $this->instance->createObject(StubObject::class);

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);
    }

    public function testClassCreateWithNamedArgs()
    {
        $this->instance->registerAttribute(StubWrapper::class, Attribute::TARGET_CLASS);

        $obj = $this->instance->createObject(
            StubObject::class,
            options: ['foo' => 'bar'],
            stub: new StubAccessible
        );

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);
        self::assertInstanceOf(StubAccessible::class, $obj->instance->stub);
        self::assertEquals(['foo' => 'bar'], $obj->instance->getOptions());
    }

    public function testObjectDecorate()
    {
        $this->instance->registerAttribute(StubWrapper::class, Attribute::TARGET_CLASS);

        $obj = $this->instance->decorateObject(new StubObject);

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);

        // phpcs:disable
        $obj = $this->instance->decorateObject(
            new
            #[StubWrapper]
            class {
                public $foo = 'bar';
            },
            ['foo' => 'bar']
        );
        // phpcs:enable

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertEquals('bar', $obj->instance->foo);
        self::assertEquals(['foo' => 'bar'], $obj->options);
    }

    public function testResolveObjectMembers()
    {
        $this->instance->registerAttribute(StubMemberResolver::class);

        $foo = new class {
            #[StubMemberResolver]
            public const FLOWER = 'sakura';

            #[StubMemberResolver]
            protected ?string $foo = 'bar';

            public array $output = [];

            #[StubMemberResolver]
            protected function hello(): string
            {
                return '';
            }
        };

        $foo = $this->instance->resolveObjectMembers($foo);

        self::assertStringSafeEquals(
            <<<EOT
ReflectionClassConstant = FLOWER
ReflectionProperty = foo
ReflectionMethod = hello
EOT,
            implode("\n", $foo->output)
        );
    }

    public function testMethocCall()
    {
        $this->instance->registerAttribute(StrUpper::class, Attribute::TARGET_PARAMETER);
        $this->instance->registerAttribute(ValueImplode::class, AttributeType::CALLABLE);

        $closure = #[ValueImplode(' ')]
        function (
            #[StrUpper]
            string $str = 'hello',
            #[StrUpper]
            ?StringObject $str2 = null
        ) {
            return [$str, (string) $str2];
        };

        $r = $this->instance->call(
            $closure,
            [
                'str2' => str('world'),
            ]
        );

        self::assertEquals('HELLO WORLD', $r);
    }

    public function testMethocCallWithReference()
    {
        $this->instance->registerAttribute(StrUpper::class, Attribute::TARGET_PARAMETER);
        $this->instance->registerAttribute(
            ValueImplode::class,
            Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION
        );

        $opt = [
            'test' => 1,
        ];

        $closure = function (array &$options = []) {
            $options['foo'] = 'bar';
        };

        $r = $this->instance->call(
            $closure,
            [
                'options' => &$opt,
            ]
        );

        self::assertEquals('bar', $opt['foo']);
    }

    public function testProperties()
    {
        $this->instance->registerAttribute(PropWrap::class, Attribute::TARGET_PROPERTY);

        $obj = new class {
            #[PropWrap(StringObject::class)]
            protected $foo = 'bar';

            public function getFoo()
            {
                return $this->foo;
            }
        };

        $obj = $this->instance->resolveProperties($obj);

        self::assertEquals(
            'BAR',
            $obj->getFoo()->toUpperCase()->__toString()
        );
    }

    public function testResolveObjectDecorateWithReflectionFunction(): void
    {
        $closure = fn() => 'foo';

        $handler = $this->instance->resolveObjectDecorate(new ReflectionFunction($closure));

        self::assertSame($closure, $handler());
    }

    public function testCallWithInvokeHandler(): void
    {
        $context = new class {
        };

        $this->instance->setInvokeHandler(
            fn(callable $callable, array $args, ?object $ctx) => [$callable(...$args), $ctx]
        );

        $r = $this->instance->call(fn(string $a) => strtoupper($a), ['a' => 'foo'], $context);

        self::assertSame(['FOO', $context], $r);
    }

    public function testCallWithContext(): void
    {
        $context = new class {
            protected string $name = 'sakura';
        };

        $closure = function () {
            return $this->name;
        };

        self::assertSame('sakura', $this->instance->call($closure, [], $context));
    }

    public function testResolveCallArguments(): void
    {
        $args = $this->instance->resolveCallArguments(
            fn(string $a, string $b = 'def') => $a . $b,
            ['x']
        );

        self::assertSame(['a' => 'x', 'b' => 'def'], $args);
    }

    public function testResolveCallArgumentsWithVariadic(): void
    {
        $args = $this->instance->resolveCallArguments(
            fn(...$items) => $items,
            ['x', 'y']
        );

        self::assertSame(['x', 'y'], $args);

        $args = $this->instance->resolveCallArguments(
            fn(string $a, ...$items) => [$a, $items],
            ['x', 'y', 'z']
        );

        self::assertSame(['x', 'y', 'z'], $args);

        $r = $this->instance->call(fn(string $a, ...$items) => [$a, $items], ['x', 'y', 'z']);

        self::assertSame(['x', ['y', 'z']], $r);
    }

    public function testResolveObjectMembersSkipNoAttributes(): void
    {
        $this->instance->registerAttribute(StubMemberResolver::class);

        $foo = new class {
            public const PLAIN = 'plain';

            public array $output = [];

            public function plain(): void
            {
            }

            #[StubMemberResolver]
            public function hello(): string
            {
                return '';
            }
        };

        $foo = $this->instance->resolveObjectMembers($foo);

        self::assertSame(['ReflectionMethod = hello'], $foo->output);
    }

    public function testRunNotInvokableAttribute(): void
    {
        $this->instance->registerAttribute(StubNotInvokable::class, Attribute::TARGET_CLASS);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('is not invokable');

        $this->instance->decorateObject(
            new
            #[StubNotInvokable]
            class {
            }
        );
    }

    // public function testResolveMethods()
    // {
    //     $this->instance->registerAttribute(StubSubscribe::class, \Attribute::TARGET_METHOD);
    //     $this->instance->resolveMethods();
    // }

    protected function setUp(): void
    {
        $this->instance = new AttributesResolver;
    }
}
