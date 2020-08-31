<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Attributes;

use PHPUnit\Framework\TestCase;
use Windwalker\DI\Test\Injection\Wrapped;
use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\Attributes\AttributesResolver;
use Windwalker\Utilities\Attributes\AttributeType;
use Windwalker\Utilities\Test\Stub\Attrs\PropWrap;
use Windwalker\Utilities\Test\Stub\Attrs\StrUpper;
use Windwalker\Utilities\Test\Stub\Attrs\StubWrapper;
use Windwalker\Utilities\Test\Stub\Attrs\ValueImplode;
use Windwalker\Utilities\Test\Stub\StubAccessible;
use Windwalker\Utilities\Test\Stub\StubObject;

use function PHPUnit\Framework\assertEquals;
use function Windwalker\str;

/**
 * The AttributeResolverTest class.
 */
class AttributesResolverTest extends TestCase
{
    protected ?AttributesResolver $instance = null;

    public function testClassCreate()
    {
        $this->instance->registerAttribute(StubWrapper::class, AttributeType::CLASSES);

        $obj = $this->instance->createObject(StubObject::class);

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);
    }

    public function testClassCreateWithNamedArgs()
    {
        $this->instance->registerAttribute(StubWrapper::class, AttributeType::CLASSES);

        $obj = $this->instance->createObject(
            StubObject::class,
            options: ['foo' => 'bar'],
            stub: new StubAccessible()
        );

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);
        self::assertInstanceOf(StubAccessible::class, $obj->instance->stub);
        self::assertEquals(['foo' => 'bar'], $obj->instance->getOptions());
    }

    public function testObjectDecorate()
    {
        $this->instance->registerAttribute(StubWrapper::class, AttributeType::CLASSES);

        $obj = $this->instance->decorateObject(new StubObject());

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertInstanceOf(StubObject::class, $obj->instance);

        $obj = $this->instance->decorateObject(
            new @@StubWrapper class {
                public $foo = 'bar';
            }
        );

        self::assertInstanceOf(StubWrapper::class, $obj);
        self::assertEquals('bar', $obj->instance->foo);
    }

    public function testMethocCall()
    {
        $this->instance->registerAttribute(StrUpper::class, AttributeType::PARAMETERS);
        $this->instance->registerAttribute(ValueImplode::class, AttributeType::FUNCTION_METHOD);

        $closure = @@ValueImplode(' ') function (@@StrUpper string $str = 'hello', @@StrUpper StringObject $str2 = null) {
            return [$str, (string) $str2];
        };

        $r = $this->instance->call($closure, [
            'str2' => str('world')
        ]);

        self::assertEquals('HELLO WORLD', $r);
    }

    public function testMethocCallWithReference()
    {
        $this->instance->registerAttribute(StrUpper::class, AttributeType::PARAMETERS);
        $this->instance->registerAttribute(ValueImplode::class, AttributeType::FUNCTION_METHOD);

        $opt = [
            'test' => 1
        ];

        $closure = function (array &$options = []) {
            $options['foo'] = 'bar';
        };

        $r = $this->instance->call($closure, [
            'options' => &$opt
        ]);

        self::assertEquals('bar', $opt['foo']);
    }

    public function testProperties()
    {
        $this->instance->registerAttribute(PropWrap::class, AttributeType::PROPERTIES);

        $obj = new class {
            @@PropWrap(StringObject::class)
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

    protected function setUp(): void
    {
        $this->instance = new AttributesResolver();
    }
}
