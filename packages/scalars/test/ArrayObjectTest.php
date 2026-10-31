<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Utilities\Iterator\PriorityQueue;

use function PHPUnit\Framework\assertEquals;
use function Windwalker\arr;

/**
 * Tests the core behavior of ArrayObject.
 */
class ArrayObjectTest extends TestCase
{
    protected ArrayObject $instance;

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = ArrayObject::range(1, 3);
    }

    protected function getAssoc(): ArrayObject
    {
        return new ArrayObject(['foo' => 'bar', 'flower' => 'sakura']);
    }

    #[Test]
    public function withResetReturnsEmptyInstance(): void
    {
        $a = $this->instance->withReset();

        self::assertEquals([], $a->dump());
    }

    #[Test]
    public function withResetReturnsNewInstanceWithGivenItems(): void
    {
        $b = $this->instance->withReset([5, 5, 5]);

        self::assertEquals([5, 5, 5], $b->dump());
    }

    #[Test]
    public function fillReplacesNullItemsWithKeyedPosition(): void
    {
        $a = $this->instance->fill(['a', 'b', null]);

        self::assertEquals(['a', 'b', 3], $a->dump());
    }

    #[Test]
    public function bindReferencesGivenArray(): void
    {
        $data = ['a', 'b', null];
        $a = $this->instance->bind($data);

        $data['foo'] = 'bar';

        self::assertEquals(['a', 'b', null, 'foo' => 'bar'], $a->dump());
    }

    #[Test]
    public function jsonSerializeEncodesItemsAsJsonArray(): void
    {
        self::assertEquals('[1,2,3]', json_encode($this->instance));
    }

    #[Test]
    public function countReturnsNumberOfItems(): void
    {
        self::assertCount(3, $this->instance);
        self::assertEquals(3, $this->instance->count());
    }

    #[Test]
    public function getIteratorIteratesItems(): void
    {
        self::assertEquals([1, 2, 3], iterator_to_array($this->instance));
    }

    #[Test]
    public function containsChecksValueLoosely(): void
    {
        self::assertTrue($this->instance->contains(1));
        self::assertTrue($this->instance->contains('2'));
        self::assertFalse($this->instance->contains('5'));
    }

    #[Test]
    public function keyExistsChecksNumericKeys(): void
    {
        self::assertTrue($this->instance->keyExists(1));
        self::assertFalse($this->instance->keyExists(3));
    }

    #[Test]
    public function keyExistsChecksAssociativeKeys(): void
    {
        $foo = $this->getAssoc();

        self::assertTrue($foo->keyExists('foo'));
        self::assertFalse($foo->keyExists('car'));
    }

    #[Test]
    public function magicSetAppendsItemByNumericKey(): void
    {
        $this->instance->__set(3, 4);

        self::assertEquals([1, 2, 3, 4], $this->instance->dump());
    }

    #[Test]
    public function magicSetAssignsItemByPropertyName(): void
    {
        $this->instance->foo = 'bar';

        self::assertEquals([1, 2, 3, 'foo' => 'bar'], $this->instance->dump());
    }

    #[Test]
    public function dumpReturnsItemsAsArray(): void
    {
        self::assertEquals([1, 2, 3], $this->instance->dump());
    }

    #[Test]
    public function dumpRecursivelyConvertsNestedArrayObjects(): void
    {
        $foo = arr(
            [
                clone $this->instance,
                clone $this->instance,
            ]
        );

        self::assertEquals(
            [
                [1, 2, 3],
                [1, 2, 3],
            ],
            $foo->dump(true)
        );
    }

    #[Test]
    public function constructStoresGivenItems(): void
    {
        self::assertEquals([1, 2, 3], (new ArrayObject([1, 2, 3]))->dump());
    }

    #[Test]
    #[DataProvider('columnProvider')]
    public function columnExtractsValuesFromItems(array $src, $column, array $except, string $message): void
    {
        self::assertEquals($except, arr($src)->column($column)->dump(), $message);
    }

    public static function columnProvider(): array
    {
        return [
            'generic array' => [
                [
                    [1, 2, 3, 4, 5],
                    [6, 7, 8, 9, 10],
                    [11, 12, 13, 14, 15],
                    [16, 17, 18, 19, 20],
                ],
                2,
                [3, 8, 13, 18],
                'Should get column #2',
            ],
            'associative array' => [
                [
                    [
                        'one' => 1,
                        'two' => 2,
                        'three' => 3,
                        'four' => 4,
                        'five' => 5,
                    ],
                    [
                        'one' => 6,
                        'two' => 7,
                        'three' => 8,
                        'four' => 9,
                        'five' => 10,
                    ],
                    [
                        'one' => 11,
                        'two' => 12,
                        'three' => 13,
                        'four' => 14,
                        'five' => 15,
                    ],
                    [
                        'one' => 16,
                        'two' => 17,
                        'three' => 18,
                        'four' => 19,
                        'five' => 20,
                    ],
                ],
                'four',
                [
                    4,
                    9,
                    14,
                    19,
                ],
                'Should get column \'four\'',
            ],
            'object array' => [
                [
                    (object) [
                        'one' => 1,
                        'two' => 2,
                        'three' => 3,
                        'four' => 4,
                        'five' => 5,
                    ],
                    (object) [
                        'one' => 6,
                        'two' => 7,
                        'three' => 8,
                        'four' => 9,
                        'five' => 10,
                    ],
                    (object) [
                        'one' => 11,
                        'two' => 12,
                        'three' => 13,
                        'four' => 14,
                        'five' => 15,
                    ],
                    (object) [
                        'one' => 16,
                        'two' => 17,
                        'three' => 18,
                        'four' => 19,
                        'five' => 20,
                    ],
                ],
                'four',
                [
                    4,
                    9,
                    14,
                    19,
                ],
                'Should get column \'four\'',
            ],
        ];
    }

    #[Test]
    public function indexOfReturnsPositionOfValue(): void
    {
        self::assertEquals(1, $this->instance->indexOf(2));
    }

    #[Test]
    public function indexOfReturnsMinusOneWhenValueIsMissing(): void
    {
        self::assertEquals(-1, $this->instance->indexOf(8));
    }

    #[Test]
    public function pipePassesInstanceToCallbackAndReturnsResult(): void
    {
        $a = $this->instance->pipe(
            function (ArrayObject $arr) {
                return $arr->append(4);
            }
        );

        self::assertEquals([1, 2, 3, 4], $a->dump());
    }

    #[Test]
    public function tapPassesInstanceToCallbackWithoutChangingResult()
    {
        $b = 1;

        $a = $this->instance->tap(
            function (ArrayObject $a) use (&$b) {
                $b += $a->first();
            }
        );

        self::assertEquals(2, $b);
    }

    #[Test]
    public function unsetRemovesItemByNumericKey(): void
    {
        unset($this->instance[1]);

        self::assertEquals([1, 3], $this->instance->values()->dump());
    }

    #[Test]
    public function unsetRemovesItemByArrayKey(): void
    {
        $a = $this->getAssoc();

        unset($a['flower']);

        self::assertEquals(['foo' => 'bar'], $a->dump());
    }

    #[Test]
    public function unsetRemovesItemByPropertyName(): void
    {
        $a = $this->getAssoc();

        unset($a->flower);

        self::assertEquals(['foo' => 'bar'], $a->dump());
    }

    #[Test]
    public function uniqueRemovesDuplicateValues(): void
    {
        $a = arr([1, 2, 1, 2, 4, 5, 6, 5, 6, 3, 2, 4, 5])->unique();

        self::assertEquals(
            [
                0 => 1,
                1 => 2,
                4 => 4,
                5 => 5,
                6 => 6,
                9 => 3,
            ],
            $a->dump()
        );
    }

    #[Test]
    public function uniqueWithSortRegularComparesArrayItems(): void
    {
        $a = arr(
            [
                [1, 2],
                [3, 4],
                [1, 2],
            ]
        )->unique(SORT_REGULAR);

        self::assertEquals(
            [
                [1, 2],
                [3, 4],
            ],
            $a->dump()
        );
    }

    #[Test]
    public function collapseFlattensNestedArrayObjects(): void
    {
        $items = new ArrayObject([
            new ArrayObject([1, 2, 3]),
            new ArrayObject([4, 5, 6]),
        ]);

        $collapsed = $items->collapse(false);

        assertEquals(
            [1, 2, 3, 4, 5, 6],
            $collapsed->dump()
        );
    }

    #[Test]
    public function collapsePreservingKeysLetsLaterItemsOverwriteEarlierOnes(): void
    {
        $items = new ArrayObject([
            new ArrayObject([1, 2, 3]),
            new ArrayObject([4, 5, 6]),
        ]);

        $collapsed = $items->collapse(true);

        assertEquals(
            [4, 5, 6],
            $collapsed->dump()
        );
    }

    #[Test]
    public function explodeSplitsStringIntoItems(): void
    {
        $a = ArrayObject::explode(',', '1,2,3');

        self::assertEquals([1, 2, 3], $a->dump());
    }

    #[Test]
    public function sumAddsNumericItems(): void
    {
        self::assertEquals(6, $this->instance->sum());
    }

    #[Test]
    public function sumReturnsZeroForNonNumericItems(): void
    {
        self::assertEquals(0, $this->getAssoc()->sum());
    }

    #[Test]
    public function avgReturnsAverageOfItems(): void
    {
        $a = $this->instance->avg();

        self::assertEquals(2, $a);
    }

    #[Test]
    public function avgHandlesNonIntegerResult(): void
    {
        $a = $this->instance->append(5, 2, 3)->avg();

        self::assertEquals(2.67, round($a, 2));
    }

    #[Test]
    public function valuesReturnsItemsWithNumericKeys(): void
    {
        self::assertEquals(['bar', 'sakura'], $this->getAssoc()->values()->dump());
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function searchReturnsKeyOfMatchingValue($exp, $search, bool $strict): void
    {
        $a = arr([1, 2, 3, true, false, '', 0, null]);

        self::assertEquals($exp, $a->search($search, $strict));
    }

    public static function searchProvider(): array
    {
        return [
            [1, 2, false],
            [0, true, false],
            [3, true, true],
            [4, false, true],
            [4, null, false],
            [7, fn($v) => $v === null, true],
        ];
    }

    #[Test]
    public function offsetGetReturnsItemByKey(): void
    {
        self::assertEquals(3, $this->instance[2]);
    }

    #[Test]
    public function offsetGetReturnsNullForMissingKey(): void
    {
        self::assertNull($this->instance[5]);
    }

    #[Test]
    public function applyReturnsNewInstanceFromCallbackResult(): void
    {
        $a = $this->instance->apply(
            function (array $v) {
                return array_reverse($v);
            }
        );

        self::assertEquals([3, 2, 1], $a->dump());
        self::assertNotSame($a, $this->instance);
    }

    #[Test]
    public function transformReplacesItemsOnSameInstance(): void
    {
        $a = $this->instance->transform(
            function (array $v) {
                return array_reverse($v);
            }
        );

        self::assertEquals([3, 2, 1], $a->dump());
        self::assertSame($a, $this->instance);
    }

    #[Test]
    public function implodeJoinsItemsIntoStringObject(): void
    {
        $str = $this->instance->implode(',');

        self::assertEquals('1,2,3', (string) $str);
    }

    #[Test]
    public function keysReturnsAllItemKeys(): void
    {
        self::assertEquals([0, 1, 2], $this->instance->keys()->dump());
        self::assertEquals(['foo', 'flower'], $this->getAssoc()->keys()->dump());
    }

    #[Test]
    public function keysWithSearchValueReturnsKeysOfMatchingItems(): void
    {
        $array = arr(['blue', 'red', 'green', 'blue', 'blue']);

        self::assertEquals([0, 3, 4], $array->keys('blue')->dump());
    }

    #[Test]
    public function keysWithStrictSearchComparesTypes(): void
    {
        $array = arr([1, '1', 2, '2', 1, '1']);

        self::assertEquals([0, 1, 4, 5], $array->keys(1)->dump());
        self::assertEquals([0, 4], $array->keys(1, true)->dump());
    }

    #[Test]
    public function offsetSetAddsNewKey(): void
    {
        $a = $this->getAssoc();

        $a->offsetSet('roo', 'koo');

        self::assertEquals(['foo' => 'bar', 'flower' => 'sakura', 'roo' => 'koo'], $a->dump());
    }

    #[Test]
    public function offsetSetOverwritesExistingKeyUsingArrayAccess(): void
    {
        $a = $this->getAssoc();

        $a['roo'] = 'koo';
        $a['roo'] = 'goo';

        self::assertEquals(['foo' => 'bar', 'flower' => 'sakura', 'roo' => 'goo'], $a->dump());
    }

    #[Test]
    public function offsetSetWithNullKeyAppendsItem(): void
    {
        $a = $this->instance;

        $a[] = 'hello';

        self::assertEquals([1, 2, 3, 'hello'], $a->dump());
    }

    #[Test]
    public function magicGetReturnsItemOrNull(): void
    {
        $a = $this->getAssoc();

        self::assertEquals('bar', $a->foo);
        self::assertNull($a->hello);
    }

    #[Test]
    public function offsetExistsChecksItemExistence(): void
    {
        self::assertTrue(isset($this->instance[2]));
        self::assertTrue(isset($this->getAssoc()['foo']));
        self::assertFalse(isset($this->getAssoc()['hello']));
    }

    #[Test]
    public function magicIssetChecksItemExistence(): void
    {
        self::assertTrue(isset($this->instance->{2}));
        self::assertTrue(isset($this->getAssoc()->foo));
        self::assertFalse(isset($this->getAssoc()->hello));
    }

    #[Test]
    public function asConvertsItemsIntoGivenClass(): void
    {
        $a = arr([1, 2, 3])->as(PriorityQueue::class);

        self::assertInstanceOf(
            PriorityQueue::class,
            $a
        );

        self::assertEquals(
            [1, 2, 3],
            $a->toArray()
        );
    }

    #[Test]
    public function createBuildsInstanceFromGivenData(): void
    {
        self::assertEquals(['a' => 1], ArrayObject::create(['a' => 1])->dump());
    }

    #[Test]
    public function withReturnsNewInstanceWithKeySet(): void
    {
        $a = $this->getAssoc();
        $b = $a->with('foo', 'yoo');

        self::assertEquals('yoo', $b['foo']);
        self::assertEquals('bar', $a['foo']);
    }

    #[Test]
    public function withDefKeepsExistingValue(): void
    {
        $b = $this->getAssoc()->withDef('foo', 'yoo');

        self::assertEquals('bar', $b['foo']);
    }

    #[Test]
    public function withDefSetsDefaultWhenKeyMissing(): void
    {
        $b = $this->getAssoc()->withDef('new', 'yoo');

        self::assertEquals('yoo', $b['new']);
    }

    #[Test]
    public function withRemoveReturnsNewInstanceWithoutKey(): void
    {
        $a = $this->getAssoc();
        $b = $a->withRemove('foo');

        self::assertEquals(['flower' => 'sakura'], $b->dump());
        self::assertArrayHasKey('foo', $a->dump());
    }

    #[Test]
    public function columnInvasiveReadsPropertiesFromObjects(): void
    {
        $a = arr([(object) ['id' => 1], (object) ['id' => 2]]);

        self::assertEquals([1, 2], $a->column('id', null, true)->dump());
    }

    #[Test]
    public function setColumnAssignsPropertyOnObjectItems(): void
    {
        $item = (object) ['id' => 1];
        $result = arr([$item])->setColumn('flag', true);

        self::assertTrue($item->flag);
        self::assertInstanceOf(ArrayObject::class, $result);
        self::assertSame($item, $result[0]);
    }

    #[Test]
    public function setColumnSetsKeyOnArrayItemsAndKeepsOtherKeys(): void
    {
        $a = arr(['x' => ['id' => 1], 'y' => ['id' => 2]]);
        $b = $a->setColumn('flag', true);

        self::assertEquals(
            ['x' => ['id' => 1, 'flag' => true], 'y' => ['id' => 2, 'flag' => true]],
            $b->dump()
        );
        self::assertEquals(['x' => ['id' => 1], 'y' => ['id' => 2]], $a->dump());
    }

    #[Test]
    public function searchWithClosureReturnsMatchedKey(): void
    {
        $a = arr(['a' => 1, 'b' => 2]);

        self::assertSame('b', $a->search(fn($v, $k) => $v === 2));
    }

    #[Test]
    public function searchWithClosureReturnsFalseWhenNothingMatches(): void
    {
        self::assertFalse(arr([1, 2])->search(fn($v) => $v > 5));
    }

    #[Test]
    public function avgReturnsNullWhenSumIsZero(): void
    {
        self::assertNull(arr([])->avg());
    }

    #[Test]
    public function wrapReturnsSameInstanceWhenAlreadyWrapped(): void
    {
        $a = arr([1]);

        self::assertSame($a, ArrayObject::wrap($a));
    }

    #[Test]
    public function wrapCreatesInstanceFromArray(): void
    {
        self::assertEquals([1], ArrayObject::wrap([1])->dump());
    }

    #[Test]
    public function tryWrapReturnsNullForNull(): void
    {
        self::assertNull(ArrayObject::tryWrap(null));
    }

    #[Test]
    public function tryWrapWrapsNonNullValue(): void
    {
        self::assertEquals([1], ArrayObject::tryWrap([1])->dump());
    }

    #[Test]
    public function isEmptyAndNotEmptyReflectStorage(): void
    {
        self::assertTrue(arr([])->isEmpty());
        self::assertFalse(arr([])->notEmpty());
        self::assertFalse(arr([1])->isEmpty());
        self::assertTrue(arr([1])->notEmpty());
    }
}
