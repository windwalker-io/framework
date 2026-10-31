<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use ArrayIterator;
use ArrayObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\Exception\CastingException;
use Windwalker\Utilities\Test\Stub\Enum\StubBackedEnum;
use Windwalker\Utilities\TypeCast;

class TypeCastTest extends TestCase
{
    use BaseAssertionTrait;

    #[Test]
    #[DataProvider('toArrayProvider')]
    public function toArrayConvertsValueToArrayWithRecursiveOption($input, $recursive, $expect)
    {
        $this->assertEquals($expect, TypeCast::toArray($input, $recursive));
    }

    public static function toArrayProvider(): array
    {
        return [
            'string' => [
                'foo',
                false,
                ['foo'],
            ],
            'array' => [
                ['foo'],
                false,
                ['foo'],
            ],
            'array_recursive' => [
                [
                    'foo' => [
                        (object) ['bar' => 'bar'],
                        (object) ['baz' => 'baz'],
                    ],
                ],
                true,
                [
                    'foo' => [
                        ['bar' => 'bar'],
                        ['baz' => 'baz'],
                    ],
                ],
            ],
            'iterator' => [
                ['foo' => new ArrayIterator(['bar' => 'baz'])],
                true,
                ['foo' => ['bar' => 'baz']],
            ],
        ];
    }

    #[Test]
    #[DataProvider('toObjectProvider')]
    public function toObjectConvertsArrayToObjectWithRecursiveOption($input, $expect, bool $recursive, string $message)
    {
        self::assertEquals($expect, TypeCast::toObject($input, $recursive), $message);
    }

    public static function toObjectProvider(): array
    {
        return [
            'single object' => [
                [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                (object) [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                false,
                'Should turn array into single object',
            ],
            'multiple objects' => [
                [
                    'first' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                (object) [
                    'first' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                true,
                'Should turn multiple dimension array into nested objects',
            ],
            'single object with class' => [
                [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                (object) [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                false,
                'Should turn array into single object',
            ],
            'multiple objects with class' => [
                [
                    'first' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                (object) [
                    'first' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => (object) [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                true,
                'Should turn multiple dimension array into nested objects',
            ],
        ];
    }

    #[Test]
    public function mapAsWrapsEachItemInGivenClass(): void
    {
        $src = [
            [
                1,
                2,
                3,
            ],
            [
                4,
                5,
                6,
            ],
        ];

        /** @var ArrayObject[] $r */
        $r = TypeCast::mapAs($src, ArrayObject::class);

        self::assertInstanceOf(ArrayObject::class, $r[0]);
        self::assertInstanceOf(ArrayObject::class, $r[1]);
        self::assertEquals([4, 5, 6], $r[1]->getArrayCopy());
    }

    /**
     * @param  mixed   $value
     * @param  mixed   $expt
     * @param  string  $type
     *
     * @see          TypeCast::try()
     */
    #[Test]
    #[DataProvider('tryProvider')]
    public function tryConvertsValueToTypeOrReturnsNullOnFailure($value, $expt, string $type): void
    {
        if ($type === 'object') {
            self::assertEquals($expt, TypeCast::try($value, $type, false));
        } else {
            self::assertSame($expt, TypeCast::try($value, $type, false));
        }
    }

    public static function tryProvider(): array
    {
        return [
            // To int
            [
                'foo',
                0,
                'int',
            ],
            [
                '3',
                3,
                'int',
            ],
            [
                '10.0',
                10,
                'int',
            ],
            [
                10.0,
                10,
                'int',
            ],
            [
                10.3,
                10,
                'int',
            ],
            [
                [],
                null,
                'int',
            ],
            [
                new stdClass(),
                null,
                'int',
            ],
            [
                true,
                1,
                'int',
            ],
            [
                false,
                0,
                'int',
            ],
            // To float
            [
                'foo',
                0.0,
                'float',
            ],
            [
                '3',
                3.0,
                'float',
            ],
            [
                '10.0',
                10.0,
                'float',
            ],
            [
                10.0,
                10.0,
                'float',
            ],
            [
                10.3,
                10.3,
                'float',
            ],
            [
                [],
                null,
                'float',
            ],
            [
                new stdClass(),
                null,
                'float',
            ],
            [
                true,
                1.0,
                'float',
            ],
            [
                false,
                0.0,
                'float',
            ],
            // To string
            [
                'foo',
                'foo',
                'string',
            ],
            [
                1,
                '1',
                'string',
            ],
            [
                1.23000,
                '1.23',
                'string',
            ],
            [
                [],
                null,
                'string',
            ],
            [
                new stdClass(),
                null,
                'string',
            ],
            [
                75e-5,
                '0.00075',
                'string',
            ],
            // Bool
            [
                'A',
                true,
                'bool',
            ],
            [
                '1',
                true,
                'bool',
            ],
            [
                '',
                false,
                'bool',
            ],
            [
                '0',
                false,
                'bool',
            ],
            [
                0,
                false,
                'bool',
            ],
            // array
            [
                'a',
                ['a'],
                'array',
            ],
            [
                123,
                [123],
                'array',
            ],
            [
                (object) ['foo' => 'bar'],
                ['foo' => 'bar'],
                'array',
            ],
            // obj
            [
                ['foo' => 'bar'],
                (object) ['foo' => 'bar'],
                'object',
            ],
            [
                $func = static function () {
                    //
                },
                $func,
                'object',
            ],
            // Other
            [
                'Hello',
                null,
                'none',
            ],
        ];
    }

    #[Test]
    public function tryIntegerShortcutConvertsNumericStringToInt()
    {
        $r = TypeCast::tryInteger('123');

        self::assertSame(123, $r);
    }

    /**
     * @param  mixed   $value
     * @param  mixed   $expt
     * @param  string  $type
     *
     * @see          TypeCast::safe()
     */
    #[Test]
    #[DataProvider('safeProvider')]
    public function safeConvertsValueToTypeWithFallbackDefault(mixed $value, mixed $expt, string $type): void
    {
        $failMsg = sprintf(
            'Safe convert %s to %s failed',
            TypeAssert::describeValue($value),
            TypeAssert::describeValue($expt)
        );

        if ($type === 'object') {
            self::assertEquals($expt, TypeCast::safe($value, $type), $failMsg);
        } else {
            self::assertSame($expt, TypeCast::safe($value, $type), $failMsg);
        }
    }

    public static function safeProvider(): array
    {
        return [
            // To int
            [
                'foo',
                null,
                'int',
            ],
            [
                '3',
                3,
                'int',
            ],
            [
                '10.0',
                10,
                'int',
            ],
            [
                10.0,
                10,
                'int',
            ],
            [
                10.3,
                null,
                'int',
            ],
            [
                [],
                null,
                'int',
            ],
            [
                new stdClass(),
                null,
                'int',
            ],
            [
                true,
                null,
                'int',
            ],
            [
                false,
                null,
                'int',
            ],
            // To float
            [
                'foo',
                null,
                'float',
            ],
            [
                '3',
                3.0,
                'float',
            ],
            [
                '10.0',
                10.0,
                'float',
            ],
            [
                10.0,
                10.0,
                'float',
            ],
            [
                10.3,
                10.3,
                'float',
            ],
            [
                [],
                null,
                'float',
            ],
            [
                new stdClass(),
                null,
                'float',
            ],
            [
                true,
                null,
                'float',
            ],
            [
                false,
                null,
                'float',
            ],
            // To string
            [
                'foo',
                'foo',
                'string',
            ],
            [
                1,
                '1',
                'string',
            ],
            [
                1.23000,
                '1.23',
                'string',
            ],
            [
                [],
                null,
                'string',
            ],
            [
                new stdClass(),
                null,
                'string',
            ],
            [
                75e-5,
                '0.00075',
                'string',
            ],
        ];
    }

    #[Test]
    #[DataProvider('mustProvider')]
    public function mustConvertsValueOrThrowsCastingException(mixed $value, mixed $expt, bool $success, string $type): void
    {
        if (!$success) {
            $this->expectException(CastingException::class);
        }

        $result = TypeCast::must($value, $type);

        if ($success) {
            if ($type === 'object') {
                self::assertEquals($expt, $result);
            } else {
                self::assertSame($expt, $result);
            }
        }
    }

    public static function mustProvider(): array
    {
        return [
            // To int
            [
                'foo',
                null,
                false,
                'int',
            ],
            [
                '3',
                3,
                true,
                'int',
            ],
            [
                '10.0',
                10,
                true,
                'int',
            ],
            [
                10.0,
                10,
                true,
                'int',
            ],
            [
                10.3,
                null,
                false,
                'int',
            ],
            [
                [],
                null,
                false,
                'int',
            ],
            [
                new stdClass(),
                null,
                false,
                'int',
            ],
            [
                true,
                null,
                false,
                'int',
            ],
            [
                false,
                null,
                false,
                'int',
            ],
            // To float
            [
                'foo',
                null,
                false,
                'float',
            ],
            [
                '3',
                3.0,
                true,
                'float',
            ],
            [
                '10.0',
                10.0,
                true,
                'float',
            ],
            [
                10.0,
                10.0,
                true,
                'float',
            ],
            [
                10.3,
                10.3,
                true,
                'float',
            ],
            [
                [],
                null,
                false,
                'float',
            ],
            [
                new stdClass(),
                null,
                false,
                'float',
            ],
            [
                true,
                null,
                false,
                'float',
            ],
            [
                false,
                null,
                false,
                'float',
            ],
            // To Numeric
            [
                123,
                123,
                true,
                'numeric',
            ],
            [
                123.123,
                123.123,
                true,
                'numeric',
            ],
            [
                '123',
                123,
                true,
                'numeric',
            ],
            [
                '123.123',
                123.123,
                true,
                'numeric',
            ],
            [
                '123string',
                null,
                false,
                'numeric',
            ],
            // To string
            [
                'foo',
                'foo',
                true,
                'string',
            ],
            [
                1,
                '1',
                true,
                'string',
            ],
            [
                1.23000,
                '1.23',
                true,
                'string',
            ],
            [
                [],
                null,
                false,
                'string',
            ],
            [
                new stdClass(),
                null,
                false,
                'string',
            ],
            [
                75e-5,
                '0.00075',
                true,
                'string',
            ],
            [
                1_234.567,
                '1234.567',
                true,
                'string',
            ]
        ];
    }

    #[Test]
    public function extractEnumReturnsBackedValueOrCaseName(): void
    {
        self::assertSame('bar', TypeCast::extractEnum(StubBackedEnum::BAR));
        self::assertSame('Flower', TypeCast::extractEnum(TypeCastUnitEnum::Flower));
    }

    #[Test]
    public function dumpAllReturnsAllPropertiesIncludingNonPublicOnes(): void
    {
        $obj = new TypeCastSubject();

        self::assertSame(['pub' => 1, 'prot' => 2, 'priv' => 3], TypeCast::dumpAll($obj));
        // Second call uses the properties cache.
        self::assertSame(['pub' => 1, 'prot' => 2, 'priv' => 3], TypeCast::dumpAll($obj));
    }

    #[Test]
    public function toArrayUsesReflectionFilterDumpableAndRecursiveDateConversion(): void
    {
        $obj = new TypeCastSubject();

        self::assertSame(['pub' => 1], TypeCast::toArray($obj));
        self::assertSame(['pub' => 1], TypeCast::toArray($obj, filter: \ReflectionProperty::IS_PUBLIC));

        $dumpable = new class () implements DumpableInterface {
            public function dump(bool $recursive = false, bool $onlyDumpable = false): array
            {
                return ['recursive' => $recursive];
            }
        };

        self::assertSame(['recursive' => false], TypeCast::toArray($dumpable));
        self::assertSame(['recursive' => true], TypeCast::toArray($dumpable, true));
        self::assertSame(
            ['date' => '2020-01-02T03:04:05.000+00:00'],
            TypeCast::toArray(['date' => new \DateTimeImmutable('2020-01-02T03:04:05+00:00')], true)
        );
    }

    #[Test]
    public function toArrayRecursiveHonorsOnlyDumpableOption(): void
    {
        $dumpable = new class () implements DumpableInterface {
            public function dump(bool $recursive = false, bool $onlyDumpable = false): array
            {
                return ['x' => 1];
            }
        };
        $plain = new TypeCastSubject();
        $src = ['a' => ['b' => $plain], 'dump' => $dumpable, 'plain' => $plain];

        self::assertSame(
            ['a' => ['b' => ['pub' => 1]], 'dump' => ['x' => 1], 'plain' => ['pub' => 1]],
            TypeCast::toArray($src, true)
        );

        $onlyDumpable = TypeCast::toArray($src, true, true);

        self::assertSame(['x' => 1], $onlyDumpable['dump']);
        self::assertSame($plain, $onlyDumpable['plain']);
        self::assertSame($plain, $onlyDumpable['a']['b']);
    }

    #[Test]
    public function toIterableKeepsIterablesAndConvertsObjectsAndScalars(): void
    {
        $iter = new ArrayIterator([1]);

        self::assertSame($iter, TypeCast::toIterable($iter));
        self::assertSame([1, 2], TypeCast::toIterable([1, 2]));
        self::assertSame(['pub' => 1], TypeCast::toIterable(new TypeCastSubject()));
        self::assertSame(['a'], TypeCast::toIterable('a'));
        self::assertSame([], TypeCast::toIterable(null));
    }

    #[Test]
    public function toStringHandlesEnumClosureArrayAndObject(): void
    {
        self::assertSame('bar', TypeCast::toString(StubBackedEnum::BAR));
        self::assertSame('Flower', TypeCast::toString(TypeCastUnitEnum::Flower));
        self::assertSame('abc', TypeCast::toString(fn() => 'abc'));
        self::assertSame('Array()', TypeCast::toString([1]));
        self::assertSame('[Object ' . TypeCastSubject::class . ']', TypeCast::toString(new TypeCastSubject()));
        self::assertSame('5', TypeCast::toString(5));
        self::assertSame('', TypeCast::toString(null));
    }

    #[Test]
    public function toStringAndForceStringDumpArraysAndObjects(): void
    {
        self::assertStringContainsString('[foo] => bar', TypeCast::toString(['foo' => 'bar'], true));
        self::assertStringContainsString('[foo] => bar', TypeCast::forceString(['foo' => 'bar']));
        self::assertStringContainsString('pub', TypeCast::forceString(new TypeCastSubject()));
        self::assertSame('plain', TypeCast::forceString('plain'));
    }

    #[Test]
    #[DataProvider('mapAsTypeProvider')]
    public function mapAsCastsEachItemToScalarType(string $type, array $expected): void
    {
        self::assertSame($expected, TypeCast::mapAs(['1', 0, '2.5'], $type));
    }

    public static function mapAsTypeProvider(): array
    {
        return [
            'string' => ['string', ['1', '0', '2.5']],
            'int' => ['INT', [1, 0, 2]],
            'integer' => ['integer', [1, 0, 2]],
            'float' => ['float', [1.0, 0.0, 2.5]],
            'double' => ['double', [1.0, 0.0, 2.5]],
            'bool' => ['bool', [true, false, true]],
            'boolean' => ['boolean', [true, false, true]],
        ];
    }

    #[Test]
    public function mapAsConvertsEachItemToArray(): void
    {
        self::assertSame([['a'], [1, 2]], TypeCast::mapAs(['a', new ArrayObject([1, 2])], 'array'));
    }

    #[Test]
    public function mapAsThrowsForUnknownType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('foo is not a valid type or class name.');

        TypeCast::mapAs([1], 'Foo');
    }

    #[Test]
    #[DataProvider('strictTryProvider')]
    public function tryWithStrictFlagRejectsUnsafeBoolArrayAndObjectConversions(
        mixed $value,
        string $type,
        mixed $expected
    ): void {
        self::assertEquals($expected, TypeCast::try($value, $type, true));
    }

    public static function strictTryProvider(): array
    {
        $obj = new stdClass();

        return [
            ['1', 'bool', null],
            [true, 'bool', true],
            ['a', 'array', null],
            [['a'], 'array', ['a']],
            ['a', 'object', null],
            [$obj, 'object', $obj],
        ];
    }

    #[Test]
    public function tryShortcutMethodsDelegateToTryWithGivenType(): void
    {
        self::assertSame(1.5, TypeCast::tryFloat('1.5'));
        self::assertSame(12, TypeCast::tryNumeric('12'));
        self::assertSame('a', TypeCast::tryString('a'));
        self::assertTrue(TypeCast::tryBoolean(1));
        self::assertSame(['a'], TypeCast::tryArray('a'));
        self::assertEquals((object) ['scalar' => 'a'], TypeCast::tryObject('a'));
        self::assertNull(TypeCast::tryInteger('abc', true));
    }

    #[Test]
    public function safeAndMustShortcutMethodsUseStrictConversion(): void
    {
        self::assertSame(1, TypeCast::safeInteger('1'));
        self::assertNull(TypeCast::safeInteger('1.5'));
        self::assertSame(1.5, TypeCast::safeFloat('1.5'));
        self::assertSame(2, TypeCast::safeNumeric('2'));
        self::assertSame('1', TypeCast::safeString(1));
        self::assertTrue(TypeCast::safeBoolean(true));
        self::assertNull(TypeCast::safeBoolean(1));
        self::assertSame(['a'], TypeCast::safeArray(['a']));
        self::assertNull(TypeCast::safeObject([]));

        self::assertSame(1, TypeCast::mustInteger('1'));
        self::assertSame(1.5, TypeCast::mustFloat('1.5'));
        self::assertSame(2, TypeCast::mustNumeric('2'));
        self::assertSame('x', TypeCast::mustString('x'));
        self::assertFalse(TypeCast::mustBoolean(false));
        self::assertSame(['a'], TypeCast::mustArray(['a']));
        $obj = new stdClass();
        self::assertSame($obj, TypeCast::mustObject($obj));
    }

    #[Test]
    public function mustShortcutThrowsCastingExceptionWhenNotSafe(): void
    {
        $this->expectException(CastingException::class);

        TypeCast::mustInteger('1.5');
    }

    #[Test]
    public function callStaticThrowsForUnknownMethod(): void
    {
        $this->expectException(\BadMethodCallException::class);

        TypeCast::tryNothing(1);
    }
}

enum TypeCastUnitEnum
{
    case Flower;
}

/**
 * Fixture with public, protected and private properties for TypeCast dump/convert tests.
 * Removable together with TypeCastTest.
 */
class TypeCastSubject
{
    public int $pub = 1;

    protected int $prot = 2;

    private int $priv = 3;
}
