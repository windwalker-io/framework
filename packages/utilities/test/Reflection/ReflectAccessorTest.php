<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Reflection;

use Attribute;
use InvalidArgumentException;
use JsonSerializable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\Test\Stub\StubReflectSubject;

/**
 * The ReflectAccessorTest class.
 */
class ReflectAccessorTest extends TestCase
{
    /**
     * @see  ReflectAccessor::setValue
     */
    #[Test]
    public function setValueWithSafeTypeCastsValuesToPropertyTypes(): void
    {
        $foo = new class () implements JsonSerializable {
            protected ?int $id;

            protected int|string|null $price;

            protected int $parentId;

            protected string $content;

            protected $foo;

            public function jsonSerialize(): array
            {
                return get_object_vars($this);
            }
        };

        ReflectAccessor::setValue($foo, 'id', '123', true);
        ReflectAccessor::setValue($foo, 'price', '123', true);
        ReflectAccessor::setValue($foo, 'parentId', 'Test', true);
        ReflectAccessor::setValue($foo, 'content', null, true);
        ReflectAccessor::setValue($foo, 'foo', null, true);

        self::assertEquals(
            '{"id":123,"price":"123","parentId":0,"content":"","foo":null}',
            json_encode($foo)
        );
    }

    /**
     * @see  ReflectAccessor::getValue
     */
    #[Test]
    public function getValueReturnsPropertyValue(): void
    {
        $object = new StubReflectSubject();

        self::assertSame('secret', ReflectAccessor::getValue($object, 'hidden'));
        self::assertSame(1, ReflectAccessor::getValue($object, 'visible'));
        self::assertSame('base', ReflectAccessor::getValue($object, 'parentPrivate'));

        $this->expectException(InvalidArgumentException::class);

        ReflectAccessor::getValue(new \stdClass(), 'missing');
    }

    /**
     * @see  ReflectAccessor::reflect
     */
    #[Test]
    public function reflectReturnsReflectionObject(): void
    {
        $object = new StubReflectSubject();

        self::assertInstanceOf(ReflectionObject::class, ReflectAccessor::reflect($object));
        self::assertInstanceOf(ReflectionClass::class, ReflectAccessor::reflect(\stdClass::class));
        self::assertInstanceOf(ReflectionFunction::class, ReflectAccessor::reflect(strlen(...)));

        $ref = new ReflectionClass($object);

        self::assertSame($ref, ReflectAccessor::reflect($ref));

        $this->expectException(InvalidArgumentException::class);

        ReflectAccessor::reflect(123);
    }

    /**
     * @see  ReflectAccessor::invoke
     */
    #[Test]
    public function invokeCallsInaccessibleMethod(): void
    {
        $object = new StubReflectSubject();

        self::assertSame('Hello World', ReflectAccessor::invoke($object, 'secretGreet', 'Hello', 'World'));
    }

    /**
     * @see  ReflectAccessor::getPropertiesValues
     */
    #[Test]
    public function getPropertiesValuesReturnsAllPropertyValues(): void
    {
        $object = new StubReflectSubject();

        self::assertSame(
            ['visible' => 1, 'hidden' => 'secret', 'uninitialized' => null],
            ReflectAccessor::getPropertiesValues($object, ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED)
        );
        self::assertSame(
            ['visible' => 1],
            ReflectAccessor::getPropertiesValues($object, ReflectionProperty::IS_PUBLIC)
        );
    }

    /**
     * @see  ReflectAccessor::getReflectProperties
     */
    #[Test]
    public function getReflectPropertiesReturnsReflectionProperties(): void
    {
        $object = new StubReflectSubject();
        $properties = ReflectAccessor::getReflectProperties($object, ReflectionProperty::IS_PUBLIC);

        self::assertSame(['visible'], array_keys($properties));
        self::assertContainsOnlyInstancesOf(ReflectionProperty::class, $properties);
        self::assertArrayHasKey('hidden', ReflectAccessor::getReflectProperties($object));
    }

    /**
     * @see  ReflectAccessor::getReflectMethods
     */
    #[Test]
    public function getReflectMethodsReturnsReflectionMethods(): void
    {
        $object = new StubReflectSubject();
        $methods = ReflectAccessor::getReflectMethods($object, ReflectionMethod::IS_PROTECTED);

        self::assertSame(['secretGreet'], array_keys($methods));
        self::assertContainsOnlyInstancesOf(ReflectionMethod::class, $methods);
        self::assertArrayHasKey('secretGreet', ReflectAccessor::getReflectMethods($object));
    }

    /**
     * @see  ReflectAccessor::getNoRepeatAttributes
     */
    #[Test]
    public function getNoRepeatAttributesReturnsNonRepeatedAttributes(): void
    {
        $ref = new ReflectionClass(new StubReflectSubject());
        $attrs = ReflectAccessor::getNoRepeatAttributes($ref);

        self::assertSame([Attribute::class], array_keys($attrs));
        self::assertSame(
            [Attribute::class],
            array_keys(ReflectAccessor::getNoRepeatAttributes($ref, Attribute::class))
        );
        self::assertSame([], ReflectAccessor::getNoRepeatAttributes($ref, \Deprecated::class));
    }

    #[Test]
    public function getPropertiesValuesWithClassNameReadsOnlyStaticProperties(): void
    {
        self::assertSame(
            ['counter' => 3, 'instanceValue' => null],
            ReflectAccessor::getPropertiesValues(ReflectAccessorStaticStub::class)
        );
        self::assertSame(
            ['counter' => 3],
            ReflectAccessor::getPropertiesValues(ReflectAccessorStaticStub::class, ReflectionProperty::IS_STATIC)
        );
    }

    #[Test]
    public function setValueWritesPrivatePropertyOfParentClass(): void
    {
        $child = new class () extends ReflectAccessorParentStub {};

        ReflectAccessor::setValue($child, 'secret', '5', true);

        self::assertSame(5, ReflectAccessor::getValue($child, 'secret'));

        ReflectAccessor::setValue($child, 'secret', 9);

        self::assertSame(9, ReflectAccessor::getValue($child, 'secret'));
    }

    #[Test]
    public function setValueCreatesDynamicPropertyWhenNotDeclared(): void
    {
        $object = new \stdClass();

        ReflectAccessor::setValue($object, 'dynamic', 'v');

        self::assertSame('v', $object->dynamic);

        $child = new #[\AllowDynamicProperties] class () extends ReflectAccessorParentStub {};

        ReflectAccessor::setValue($child, 'other', 'x');

        self::assertSame('x', $child->other);
    }

    #[Test]
    #[DataProvider('safeTypeCastProvider')]
    public function setValueWithSafeCastsAccordingToDeclaredType(string $property, mixed $input, mixed $expected): void
    {
        $object = new ReflectAccessorTypedStub();

        ReflectAccessor::setValue($object, $property, $input, true);

        self::assertEquals($expected, ReflectAccessor::getValue($object, $property));
    }

    public static function safeTypeCastProvider(): array
    {
        $date = new \DateTimeImmutable('2020-01-01');

        return [
            'object of declared class is kept' => ['date', $date, $date],
            'mixed is kept' => ['anything', '123', '123'],
            'null for nullable' => ['nullableInt', null, null],
            'empty string for nullable int' => ['nullableInt', '', null],
            'empty string for nullable float' => ['nullableFloat', '', null],
            'string to nullable int' => ['nullableInt', '42', 42],
            'non builtin type keeps non matching value' => ['date', 'not-date-object', 'not-date-object'],
            'null for non nullable is zero value' => ['count', null, 0],
            'untyped property' => ['untyped', '1', '1'],
        ];
    }

    #[Test]
    public function hasPropertyHandlesObjectsWithoutParentClass(): void
    {
        $object = new class () {
            public int $foo = 1;
        };

        self::assertTrue(ReflectAccessor::hasProperty($object, 'foo'));
        self::assertFalse(ReflectAccessor::hasProperty($object, 'bar'));
        self::assertFalse(ReflectAccessor::hasProperty(new \stdClass(), 'bar'));
    }

    #[Test]
    public function hasPropertyChecksParentClassProperties(): void
    {
        $child = new class () extends ReflectAccessorParentStub {};

        self::assertTrue(ReflectAccessor::hasProperty($child, 'secret'));
        self::assertFalse(ReflectAccessor::hasProperty($child, 'missing'));
    }
}

class ReflectAccessorParentStub
{
    private int $secret = 1;
}

/**
 * Fixture with a static and an instance property, used by ReflectAccessorTest to verify
 * getPropertiesValues() with a class name.
 *
 * Remove it together with that test.
 */
class ReflectAccessorStaticStub
{
    public static int $counter = 3;

    public int $instanceValue;
}

/**
 * Fixture with differently typed properties, used by ReflectAccessorTest to verify
 * setValue() safe type casting.
 *
 * Remove it together with that test.
 */
class ReflectAccessorTypedStub
{
    public \DateTimeImmutable|string $date;

    public mixed $anything;

    public ?int $nullableInt;

    public ?float $nullableFloat;

    public int $count;

    public $untyped;
}
