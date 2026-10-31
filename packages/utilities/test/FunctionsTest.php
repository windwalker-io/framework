<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Arr;

use Windwalker\Utilities\Attributes\Expose;
use Windwalker\Utilities\Attributes\Invisible;
use Windwalker\Utilities\Compare\WhereWrapper;
use Windwalker\Utilities\Piping;
use Windwalker\Utilities\Proxy\CachedCallable;
use Windwalker\Utilities\Proxy\DisposableCallable;
use Windwalker\Utilities\Test\Stub\Enum\StubBackedEnum;
use Windwalker\Utilities\Test\Stub\Enum\StubMyclabsEnum;
use Windwalker\Utilities\Wrapper\DepthWrapper;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\ValueReference;

use function show;
use function Windwalker\cachable;
use function Windwalker\clamp;
use function Windwalker\count;
use function Windwalker\depth;
use function Windwalker\disposable;
use function Windwalker\get_object_dump_props;
use function Windwalker\get_object_dump_values;
use function Windwalker\has_attributes;
use function Windwalker\has_used_trait;
use function Windwalker\iterator_keys;
use function Windwalker\nope;
use function Windwalker\pipe;
use function Windwalker\piping;
use function Windwalker\raw;
use function Windwalker\ref;
use function Windwalker\tap;
use function Windwalker\tid;
use function Windwalker\trait_uses;
use function Windwalker\uid;
use function Windwalker\unwrap;
use function Windwalker\unwrap_enum;
use function Windwalker\value;
use function Windwalker\value_compare;
use function Windwalker\where;

/**
 * Tests for the global PHP methods.
 */
class FunctionsTest extends TestCase
{
    use BaseAssertionTrait;

    #[Test]
    public function showPrintsValuesAndTruncatesAtMaxDepth(): void
    {
        $data = [
            'test',
            1,
            2,
            ['foo' => 'bar'],
            ['max' => ['level' => ['test' => ['this' => ['no' => 'show']]]]],
            depth(4),
        ];

        $expected = <<<OUT
[Value 1]
test

[Value 2]
1

[Value 3]
2

[Value 4]
Array
(
    [foo] => bar
)

[Value 5]
Array
(
    [max] => Array
        (
            [level] => Array
                (
                    [test] => Array
                        (
                            [this] => Array
                                (
                                    *MAX LEVEL*
                                )

                        )

                )

        )

)
OUT;

        Arr::$output = fopen('php://memory', 'wb+');
        show(...$data);
        rewind(Arr::$output);

        $c = stream_get_contents(Arr::$output);
        self::assertStringSafeEquals($expected, $c);

        fclose(Arr::$output);
    }

    #[Test]
    public function showPrintsNamedArgumentsWithDepthOption(): void
    {
        $expected = <<<OUT
[Value 1]
Hello

[foo]
bar

[yoo]
Array
(
    [A] => Array
        (
            [B] => Array
                (
                    [C] => Array
                        (
                            *MAX LEVEL*
                        )

                )

        )

)
OUT;

        Arr::$output = fopen('php://memory', 'wb+');
        show(
            'Hello',
            foo: 'bar',
            yoo: [
                'A' => [
                    'B' => [
                        'C' => [
                            'D' => [
                                'E' => 'F'
                            ]
                        ]
                    ]
                ]
            ],
            depth: depth(3)
        );
        rewind(Arr::$output);

        $c = stream_get_contents(Arr::$output);
        self::assertStringSafeEquals($expected, $c);

        fclose(Arr::$output);
    }

    #[Test]
    #[DataProvider('clampProvider')]
    public function clampLimitsNumberWithinMinAndMax(int|float $num, int|float|null $min, int|float|null $max, int|float $expected): void
    {
        self::assertEquals(
            $expected,
            clamp($num, $min, $max)
        );
    }

    public static function clampProvider(): array
    {
        return [
            [
                50,
                10,
                20,
                20
            ],
            [
                5,
                10,
                20,
                10
            ],
            [
                50,
                10,
                null,
                50
            ],
            [
                10,
                null,
                50,
                10
            ]
        ];
    }

    #[Test]
    public function clampThrowsOnInvalidBounds(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessageIsOrContains('must be smaller than or equal to');

        clamp(5, 20, 10);
    }

    #[Test]
    #[DataProvider('clampNanProvider')]
    public function clampThrowsOnNanBounds(float $min, float $max, string $message): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessageIsOrContains($message);

        clamp(5, $min, $max);
    }

    public static function clampNanProvider(): array
    {
        return [
            'min' => [NAN, 10.0, '$min) must not be NAN'],
            'max' => [1.0, NAN, '$max) must not be NAN'],
        ];
    }

    #[Test]
    #[DataProvider('stringableProvider')]
    public function isStringableDetectsValuesCastableToString(mixed $value, bool $expected): void
    {
        self::assertSame($expected, is_stringable($value));
    }

    public static function stringableProvider(): array
    {
        return [
            'array' => [[], false],
            'plain object' => [new \stdClass(), false],
            'stringable object' => [new class {
                public function __toString(): string
                {
                    return 'x';
                }
            }, true],
            'resource' => [STDIN, false],
            'string' => ['a', true],
            'int' => [1, true],
            'null' => [null, true],
        ];
    }

    #[Test]
    public function isJsonValidatesStringsOnly(): void
    {
        self::assertTrue(is_json('{"a":1}'));
        self::assertFalse(is_json('{a:1}'));
        self::assertFalse(is_json(['a']));
        self::assertFalse(is_json(null));
    }

    #[Test]
    public function isScalarOrNullAcceptsScalarsAndNull(): void
    {
        self::assertTrue(is_scalar_or_null('a'));
        self::assertTrue(is_scalar_or_null(1.5));
        self::assertTrue(is_scalar_or_null(null));
        self::assertFalse(is_scalar_or_null([]));
        self::assertFalse(is_scalar_or_null(new \stdClass()));
    }

    #[Test]
    public function nopeReturnsFirstArgumentOrNull(): void
    {
        $fn = nope();

        self::assertSame('a', $fn('a', 'b'));
        self::assertNull($fn());
    }

    #[Test]
    public function tapReturnsOriginalValueAfterCallingCallback(): void
    {
        $seen = null;

        self::assertSame('v', tap('v', function ($v) use (&$seen) {
            $seen = $v;

            return 'other';
        }));
        self::assertSame('v', $seen);
    }

    #[Test]
    public function pipeReturnsCallbackResultAndPipingWrapsValue(): void
    {
        self::assertSame(4, pipe(2, fn($v) => $v * 2));
        self::assertInstanceOf(Piping::class, piping(1));
    }

    #[Test]
    public function countHandlesNullTraversableAndCountable(): void
    {
        self::assertSame(0, count(null));
        self::assertSame(3, count(new \ArrayIterator([1, 2, 3])));
        self::assertSame(3, count((fn() => yield from [1, 2, 3])()));
        self::assertSame(2, count([1, [2, 3]]));
        self::assertSame(4, count([1, [2, 3]], COUNT_RECURSIVE));
    }

    #[Test]
    public function uidGeneratesUniqueIdsWithPrefix(): void
    {
        $a = uid('p_');
        $b = uid('p_');

        self::assertStringStartsWith('p_', $a);
        self::assertSame(2 + 24, strlen($a));
        self::assertNotSame($a, $b);
    }

    #[Test]
    public function uidAndTidWithTimebaseGenerateUniqueIdsInTimeOrder(): void
    {
        $ids = [uid('', true), tid(), tid('t_')];

        self::assertCount(3, array_unique($ids));
        self::assertStringStartsWith('t_', $ids[2]);
        self::assertMatchesRegularExpression('/^[0-9ab]+[0-9a-f]{8}$/', $ids[0]);
    }

    #[Test]
    public function iteratorKeysReturnsKeysOfTraversable(): void
    {
        self::assertSame(['a', 'b'], iterator_keys(new \ArrayIterator(['a' => 1, 'b' => 2])));
    }

    #[Test]
    public function whereCreatesComparingWrapper(): void
    {
        $where = where('foo', '>=', 3);

        self::assertInstanceOf(WhereWrapper::class, $where);
        self::assertTrue($where(['foo' => 3]));
        self::assertFalse($where(['foo' => 2]));
        self::assertFalse(where('foo', '=', 3, true)(['foo' => '3']));
    }

    #[Test]
    public function valueUnwrapsEnumsWeakReferencesWrappersAndClosures(): void
    {
        $object = new \stdClass();
        $weak = \WeakReference::create($object);

        self::assertSame('bar', value(StubBackedEnum::BAR));
        self::assertSame('FOO', value(UnitStatus::FOO));
        self::assertSame('bar', value(new StubMyclabsEnum('bar')));
        self::assertSame($object, value($weak));
        self::assertSame(1, value(ref('x'), ['x' => 1]));
        self::assertSame('raw', value(raw('raw')));
        self::assertSame(5, value(fn(int $a, int $b) => $a + $b, 2, 3));
        self::assertSame(3, value(cachable(fn() => 3)));
        self::assertSame('plain', value('plain'));
    }

    #[Test]
    public function unwrapEnumExtractsBackedValueOrNameAndKeepsOtherValues(): void
    {
        self::assertSame('yoo', unwrap_enum(StubBackedEnum::YOO));
        self::assertSame('FOO', unwrap_enum(UnitStatus::FOO));
        self::assertSame('str', unwrap_enum('str'));
    }

    #[Test]
    public function unwrapInvokesOnlyWrappers(): void
    {
        self::assertSame(1, unwrap(ref('a'), ['a' => 1]));
        $closure = fn() => 1;
        self::assertSame($closure, unwrap($closure));
        self::assertSame('plain', unwrap('plain'));
    }

    #[Test]
    public function wrapperHelpersCreateWrapperObjects(): void
    {
        self::assertInstanceOf(RawWrapper::class, raw('x'));
        self::assertSame('x', unwrap(raw('x')));
        self::assertInstanceOf(DepthWrapper::class, depth(3));

        $ref = ref('a/b', '/');
        self::assertInstanceOf(ValueReference::class, $ref);
        self::assertSame('c', $ref(['a' => ['b' => 'c']]));
    }

    #[Test]
    public function disposableCallableRunsOnlyOnceAndCachableRemembersResult(): void
    {
        $count = 0;
        $once = disposable(function () use (&$count) {
            return ++$count;
        });
        $cached = cachable(function () use (&$count) {
            return ++$count;
        });

        self::assertInstanceOf(DisposableCallable::class, $once);
        self::assertSame(1, $once());
        self::assertNull($once());

        self::assertInstanceOf(CachedCallable::class, $cached);
        self::assertSame(2, $cached());
        self::assertSame(2, $cached());
        self::assertSame(2, $count);
    }

    #[Test]
    public function valueCompareDelegatesToCompareHelper(): void
    {
        self::assertSame(-1, value_compare(1, 2));
        self::assertTrue(value_compare(2, 1, '>'));
        self::assertFalse(value_compare(2, 1, '<'));
    }

    #[Test]
    public function hasAttributesChecksReflectorsAndClassesWithOptionalInstanceof(): void
    {
        $prop = new \ReflectionProperty(DumpSubject::class, 'exposed');

        self::assertTrue(has_attributes($prop, Expose::class));
        self::assertFalse(has_attributes($prop, ExposeChild::class));
        self::assertFalse(has_attributes($prop, Invisible::class));
        self::assertTrue(has_attributes(new \ReflectionProperty(DumpSubject::class, 'child'), Expose::class, true));
        self::assertFalse(has_attributes(new \ReflectionProperty(DumpSubject::class, 'child'), Expose::class));

        self::assertTrue(has_attributes(DumpSubject::class, \AllowDynamicProperties::class));
        self::assertTrue(has_attributes(new DumpSubject(), \AllowDynamicProperties::class));
        self::assertFalse(has_attributes(new \stdClass(), \Attribute::class));
    }

    #[Test]
    public function traitUsesAndHasUsedTraitReportRecursiveTraits(): void
    {
        self::assertSame([DumpOuterTrait::class, DumpInnerTrait::class], trait_uses(DumpSubject::class));
        self::assertTrue(has_used_trait(new DumpSubject(), DumpInnerTrait::class));
        self::assertFalse(has_used_trait(DumpSubject::class, \Countable::class));
    }

    #[Test]
    public function getObjectDumpPropsFiltersHiddenNonPublicAndVirtualProperties(): void
    {
        $props = get_object_dump_props(DumpSubject::class, null, $ignores);

        self::assertSame(['pub', 'exposed', 'child', 'uninit'], array_keys($props));
        self::assertSame(['prot', 'hidden', 'virtual'], array_keys($ignores));

        // Cached result keeps ignores in sync
        $ignores = [];
        get_object_dump_props(new DumpSubject(), null, $ignores);
        self::assertSame(['prot', 'hidden', 'virtual'], array_keys($ignores));
    }

    #[Test]
    public function getObjectDumpValuesReturnsVisibleInitializedValuesIncludingExposedOnes(): void
    {
        $subject = new DumpSubject();

        $expected = ['pub' => 'a', 'child' => 'e', 'exposed' => 'c'];

        self::assertSame($expected, get_object_dump_values($subject));
        // second call uses the internal cache
        self::assertSame($expected, get_object_dump_values($subject));

        $subject->uninit = 'now';
        self::assertEquals($expected + ['uninit' => 'now'], get_object_dump_values($subject));
    }
}

enum UnitStatus
{
    case FOO;
}

/**
 * Attribute subclass used to verify the instanceof flag of has_attributes(). Removable with FunctionsTest.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ExposeChild extends Expose {}

trait DumpInnerTrait {}

trait DumpOuterTrait
{
    use DumpInnerTrait;
}

/**
 * Fixture covering public, protected, #[Expose], #[Invisible], uninitialized and virtual properties for the
 * get_object_dump_* and attribute/trait helper tests. Removable together with those tests in FunctionsTest.
 */
#[\AllowDynamicProperties]
class DumpSubject
{
    use DumpOuterTrait;

    public string $pub = 'a';

    protected string $prot = 'b';

    #[Expose]
    protected string $exposed = 'c';

    #[Invisible]
    public string $hidden = 'd';

    #[ExposeChild]
    public string $child = 'e';

    public string $uninit;

    public string $virtual {
        get => 'v';
    }
}
