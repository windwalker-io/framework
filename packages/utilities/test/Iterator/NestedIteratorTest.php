<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Context\Loop;
use Windwalker\Utilities\Iterator\NestedIterator;

class NestedIteratorTest extends TestCase
{
    /**
     * @see  NestedIterator::__construct
     */
    #[Test]
    public function wrapChainsGeneratorCallbacksOverInnerIterator(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->wrap(
            // Map
            static function ($iterator) {
                foreach ($iterator as $item) {
                    yield strtoupper($item);
                }
            }
        )
            ->wrap(
                // Filter
                static function ($iterator) {
                    foreach ($iterator as $item) {
                        if ($item !== 'D') {
                            yield $item;
                        }
                    }
                }
            );

        self::assertEquals(
            ['A', 'B', 'C', 'E', 'F'],
            iterator_to_array($iter)
        );
    }

    #[Test]
    public function eachPassesKeyAndValueToCallback(): void
    {
        $iter = new NestedIterator([1, 2, 3]);

        $r = [];
        $iter->each(
            function ($v, $k) use (&$r) {
                return $r[] = $k . '-' . $v;
            }
        );

        self::assertEquals(
            [
                '0-1',
                '1-2',
                '2-3',
            ],
            $r
        );

    }

    #[Test]
    public function eachStopsLoopWhenLoopStopIsCalled(): void
    {
        $iter = new NestedIterator([1, 2, 3]);

        $r = [];
        $iter->each(
            static function ($v, $k, Loop $loop) use (&$r) {
                if ($v > 1) {
                    return $loop->stop();
                }

                return $r[] = $k . '-' . $v;
            }
        );

        self::assertEquals(
            [
                '0-1',
            ],
            $r
        );

    }

    #[Test]
    public function eachStopsOuterLoopWhenNestedLoopStopsWithLevelTwo(): void
    {
        $a = new NestedIterator(
            [
                new NestedIterator([1, 2, 3]),
                new NestedIterator([4, 5, 6]),
                new NestedIterator([7, 8, 9]),
                new NestedIterator([10, 11, 12]),
            ]
        );

        $r = [];

        $a->each(
            function (NestedIterator $v, $k, $l) use (&$r) {
                $v->each(
                    function ($v, $k, Loop $loop) use (&$r) {
                        if ($v === 4) {
                            $loop->stop(2);

                            return;
                        }

                        $r[] = $v;
                    }
                );
            }
        );

        self::assertEquals(
            [1, 2, 3],
            $r
        );
    }

    #[Test]
    public function mapAppliesCallbackToEachItem(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->map('strtoupper');

        self::assertEquals(
            ['A', 'B', 'C', 'D', 'E', 'F'],
            iterator_to_array($iter)
        );
    }

    #[Test]
    public function flatMapFlattensCallbackResults(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->flatMap(
            fn($v) => [strtoupper($v), $v]
        );

        self::assertEquals(
            ['A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e', 'F', 'f'],
            iterator_to_array($iter, false)
        );
    }

    #[Test]
    public function mapWithKeyUsesArrayKeysReturnedByCallback(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f'])
            ->mapWithKey(fn($item) => [$item => strtoupper($item)]);

        self::assertEquals(
            [
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
                'd' => 'D',
                'e' => 'E',
                'f' => 'F',
            ],
            iterator_to_array($iter)
        );

    }

    #[Test]
    public function mapWithKeyKeepsKeysYieldedByGeneratorCallback(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f'])
            ->mapWithKey(fn($item) => yield $item => strtoupper($item));

        self::assertEquals(
            [
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
                'd' => 'D',
                'e' => 'E',
                'f' => 'F',
            ],
            iterator_to_array($iter)
        );

    }

    #[Test]
    public function mapWithKeyDiscardsKeysWhenCallbackYieldsNullKey(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f'])
            ->mapWithKey(fn($item) => yield null => strtoupper($item));

        self::assertEquals(
            [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
            ],
            iterator_to_array($iter)
        );
    }

    /**
     * @see  NestedIterator::slice
     */
    #[Test]
    public function sliceWithOffsetAndLengthReturnsFirstItems(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);

        $sliced = $iter->slice(0, 3);

        self::assertEquals(['a', 'b', 'c'], iterator_to_array($sliced));

    }

    #[Test]
    public function sliceWithoutLengthReturnsRemainingItems(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);

        $sliced = $iter->slice(3);

        self::assertEquals(['d', 'e', 'f'], iterator_to_array($sliced));

    }

    #[Test]
    public function sliceWithOffsetAndLengthReturnsMiddleItems(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);

        $sliced = $iter->slice(2, 2);

        self::assertEquals(['c', 'd'], iterator_to_array($sliced));
    }

    /**
     * @see  NestedIterator::explode
     */
    #[Test]
    public function explodeSplitsStringIntoItems(): void
    {
        $iter = NestedIterator::explode('|', 'foo|bar|yoo|goz');

        self::assertEquals(
            [
                'foo',
                'bar',
                'yoo',
                'goz',
            ],
            iterator_to_array($iter)
        );
    }

    #[Test]
    public function explodeWithLimitKeepsRemainderInLastItem(): void
    {
        $iter = NestedIterator::explode('|', 'foo|bar|yoo|goz', 2);

        self::assertEquals(
            [
                'foo',
                'bar|yoo|goz',
            ],
            iterator_to_array($iter)
        );
    }

    /**
     * @see  NestedIterator::reduce
     */
    #[Test]
    public function reduceFoldsItemsWithInitialValue(): void
    {
        $iter = new NestedIterator([1, 2, 3, 4, 5]);

        $r = $iter->reduce(
            function ($sum, $value) {
                return $sum + $value;
            },
            3
        );

        self::assertEquals(18, $r);
    }

    /**
     * @see  NestedIterator::concat
     */
    #[Test]
    public function concatAppendsArraysAndIterators(): void
    {
        $iter = new NestedIterator([1, 2, 3]);

        $iter = $iter->concat(
            [4, 5, 6],
            new NestedIterator([7, 8, 9])
        );

        self::assertEquals(
            [1, 2, 3, 4, 5, 6, 7, 8, 9],
            iterator_to_array($iter)
        );
    }

    #[Test]
    public function limitWithZeroOffsetReturnsFirstItems(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);

        $limited = $iter->limit(0, 3);

        self::assertEquals(['a', 'b', 'c'], iterator_to_array($limited));

    }

    #[Test]
    public function limitWithOffsetSkipsItemsAndPreservesKeys(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);

        $limited = $iter->limit(2, 2);

        self::assertEquals([2 => 'c', 3 => 'd'], iterator_to_array($limited));
    }

    #[Test]
    public function regexKeepsItemsMatchingPattern(): void
    {
        $iter = new NestedIterator(['foo', 'bar', 'baz', 'yoo', 'goz']);

        $filtered = $iter->regex('/^ba/');

        self::assertEquals([1 => 'bar', 2 => 'baz'], iterator_to_array($filtered));
    }

    #[Test]
    public function toCachingIteratorCachesWrappedItems(): void
    {
        // Test PHP CachingIterator
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f'])
            ->map(strtoupper(...))
            ->toCachingIterator(\CachingIterator::FULL_CACHE);

        self::assertEquals(
            ['A', 'B', 'C', 'D', 'E', 'F'],
            iterator_to_array($iter)
        );
        self::assertEquals(
            ['A', 'B', 'C', 'D', 'E', 'F'],
            $iter->getCache()
        );
    }

    #[Test]
    public function parallelZipsIteratorsFromArray(): void
    {
        $iter1 = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter2 = new NestedIterator([1, 2, 3]);

        $iter = $iter1->parallel([$iter2])
            ->mapWithKey(
                fn($v, $k) => yield null => implode('', $v)
            );

        self::assertEquals(
            ['a1', 'b2', 'c3'],
            iterator_to_array($iter)
        );

    }

    #[Test]
    public function parallelAttachesIteratorsWithCallback(): void
    {
        $iter1 = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter2 = new NestedIterator([1, 2, 3]);

        $iter = $iter1->parallel(
            function (\MultipleIterator $mi, \Iterator $it) use ($iter2) {
                $mi->attachIterator($it);
                $mi->attachIterator($iter2);
            }
        )
            ->mapWithKey(
                fn($v, $k) => yield null => implode('', $v)
            );

        self::assertEquals(
            ['a1', 'b2', 'c3'],
            iterator_to_array($iter)
        );
    }

    #[Test]
    public function infiniteRepeatsItemsEndlessly(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c'])
            ->infinite();

        $r = [];
        $i = 0;

        foreach ($iter as $item) {
            $r[] = $item;
            $i++;

            if ($i > 10) {
                break;
            }
        }

        self::assertEquals(
            ['a', 'b', 'c', 'a', 'b', 'c', 'a', 'b', 'c', 'a', 'b'],
            $r
        );
    }

    /**
     * @see  NestedIterator::implode
     */
    #[Test]
    public function implodeJoinsItemsWithGlue(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);

        $str = $iter->implode('|');

        self::assertEquals(
            'a|b|c|d|e|f',
            (string) $str
        );
    }

    /**
     * @see  NestedIterator::keys
     */
    #[Test]
    public function keysReturnsIteratorKeys(): void
    {
        $iter = new NestedIterator(['a' => 'foo', 'b' => 'bar', 'c' => 'baz']);

        self::assertEquals(
            ['a', 'b', 'c'],
            iterator_to_array($iter->keys())
        );
    }

    /**
     * @see  NestedIterator::values
     */
    #[Test]
    public function valuesReturnsIteratorValues(): void
    {
        $iter = new NestedIterator(['a' => 'foo', 'b' => 'bar', 'c' => 'baz']);

        self::assertEquals(
            ['foo', 'bar', 'baz'],
            iterator_to_array($iter->values())
        );
    }

    #[Test]
    public function filterRemovesRejectedItemsAndKeepsKeys(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->filter(
            static fn($item) => $item !== 'd'
        );

        self::assertEquals(
            ['a', 'b', 'c', 4 => 'e', 5 => 'f'],
            iterator_to_array($iter)
        );
    }

    #[Test]
    public function rewindRestartsWrappedIteration(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->wrap(
            static function ($iterator) {
                foreach ($iterator as $item) {
                    yield strtoupper($item);
                }
            }
        )
            ->wrap(
                static function ($iterator) {
                    foreach ($iterator as $item) {
                        if ($item !== 'D') {
                            yield $item;
                        }
                    }
                }
            );

        iterator_to_array($iter);

        self::assertNull($iter->current());

        $iter->rewind();

        self::assertEquals('A', $iter->current());
    }

    #[Test]
    public function rewindThrowsWhenSourceIsClosedGenerator(): void
    {
        $gen = function () {
            foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $item) {
                yield $item;
            }
        };

        $iter = new NestedIterator($gen());
        $iter = $iter->wrap(
            static function ($iterator) {
                foreach ($iterator as $item) {
                    yield strtoupper($item);
                }
            }
        );

        iterator_to_array($iter);

        self::assertNull($iter->current());

        $iter->rewind();

        $this->expectExceptionMessage('Cannot traverse an already closed generator');

        self::assertEquals('A', $iter->current());
    }

    #[Test]
    public function rewindRestartsIterationFromGeneratorCallback(): void
    {
        $gen = function () {
            foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $item) {
                yield $item;
            }
        };

        $iter = new NestedIterator($gen);
        $iter = $iter->wrap(
            static function ($iterator) {
                foreach ($iterator as $item) {
                    yield strtoupper($item);
                }
            }
        );

        iterator_to_array($iter);

        self::assertNull($iter->current());

        $iter->rewind();

        self::assertEquals('A', $iter->current());
    }

    #[Test]
    public function chunkSplitsItemsIntoGroups(): void
    {
        $gen = $this->createChunkSource();

        $iter = new NestedIterator($gen);
        $iter = $iter->chunk(4);
        $r = [];

        foreach ($iter as $i => $item) {
            $r2 = [];
            foreach ($item as $key => $value) {
                $r2[$key] = $value;
            }

            $r[$i] = $r2;
        }

        self::assertEquals(
            [
                ['A', 'B', 'C', 'D'],
                ['E', 'F'],
            ],
            $r
        );

    }

    #[Test]
    public function chunkWithPreserveKeysKeepsOriginalKeys(): void
    {
        $gen = $this->createChunkSource();

        $iter = new NestedIterator($gen);
        $iter = $iter->chunk(4, true);
        $r = [];

        foreach ($iter as $i => $item) {
            $r2 = [];
            foreach ($item as $key => $value) {
                $r2[$key] = $value;
            }

            $r[$i] = $r2;
        }

        self::assertEquals(
            [
                [
                    'a' => 'A',
                    'b' => 'B',
                    'c' => 'C',
                    'd' => 'D',
                ],
                [
                    'e' => 'E',
                    'f' => 'F',
                ],
            ],
            $r
        );
    }

    /**
     * @see  NestedIterator::filter
     */
    #[Test]
    #[DataProvider('filterFlagsProvider')]
    public function filterPassesArgumentsAccordingToFlags(int $flags, callable $callback, array $expected): void
    {
        $iter = new NestedIterator(['a' => 1, 'b' => 2, 'c' => 3])->filter($callback, $flags);

        self::assertSame($expected, iterator_to_array($iter));
    }

    public static function filterFlagsProvider(): array
    {
        return [
            'value only' => [0, static fn($v) => $v > 1, ['b' => 2, 'c' => 3]],
            'key only' => [ARRAY_FILTER_USE_KEY, static fn($k) => $k !== 'b', ['a' => 1, 'c' => 3]],
            'both' => [ARRAY_FILTER_USE_BOTH, static fn($v, $k) => $k === 'a' || $v === 3, ['a' => 1, 'c' => 3]],
        ];
    }

    /**
     * @see  NestedIterator::concat
     */
    #[Test]
    public function concatReindexesNumericKeysAndKeepsStringKeys(): void
    {
        $iter = new NestedIterator(['a', 'x' => 'b'])->concat(['c', 'y' => 'd']);

        self::assertSame([0 => 'a', 'x' => 'b', 1 => 'c', 'y' => 'd'], iterator_to_array($iter));
    }

    /**
     * @see  NestedIterator::chain
     */
    #[Test]
    public function chainIsAliasOfConcat(): void
    {
        $iter = new NestedIterator([1, 2])->chain([3], [4]);

        self::assertSame([1, 2, 3, 4], iterator_to_array($iter));
    }

    /**
     * @see  NestedIterator::slice
     */
    #[Test]
    public function sliceKeepsStringKeysAndReindexesNumericKeys(): void
    {
        $iter = new NestedIterator([10, 'a' => 20, 30, 40])->slice(1, 2);

        self::assertSame(['a' => 20, 0 => 30], iterator_to_array($iter));
    }

    /**
     * @see  NestedIterator::slice
     */
    #[Test]
    public function sliceWithZeroLengthYieldsNothing(): void
    {
        $iter = new NestedIterator([1, 2, 3])->slice(0, 0);

        self::assertSame([], iterator_to_array($iter));
    }

    /**
     * @see  NestedIterator::slice
     */
    #[Test]
    #[DataProvider('invalidSliceProvider')]
    public function sliceRejectsNegativeOffsetOrLength(int $start, ?int $length, string $message): void
    {
        $iter = new NestedIterator([1, 2, 3])->slice($start, $length);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains($message);

        iterator_to_array($iter);
    }

    public static function invalidSliceProvider(): array
    {
        return [
            'negative start' => [-1, null, 'Start offset must be non-negative'],
            'negative length' => [0, -1, 'Length must be non-negative'],
        ];
    }

    /**
     * @see  NestedIterator::explode
     */
    #[Test]
    public function explodeRejectsEmptySeparator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Separator must be non-empty string');

        NestedIterator::explode('', 'abc');
    }

    /**
     * @see  NestedIterator::parallel
     */
    #[Test]
    public function parallelResolvesCallableIteratorsAndZipsValues(): void
    {
        $iter = new NestedIterator([1, 2])->parallel([[10, 20], static fn() => new \ArrayIterator([100, 200])]);

        self::assertSame([[1, 10, 100], [2, 20, 200]], iterator_to_array($iter, false));
    }

    /**
     * @see  NestedIterator::parallel
     */
    #[Test]
    public function parallelRejectsNonTraversableCallableResult(): void
    {
        $iter = new NestedIterator([1, 2])->parallel([static fn() => 'foo']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Iterator must be callable or iterable');

        iterator_to_array($iter);
    }

    /**
     * @see  NestedIterator::checkRewindable
     */
    #[Test]
    public function checkRewindableTreatsOuterIteratorWithoutInnerAsRewindable(): void
    {
        $outer = new class () implements \OuterIterator {
            public function getInnerIterator(): ?\Iterator
            {
                return null;
            }

            public function current(): mixed
            {
                return null;
            }

            public function next(): void {}

            public function key(): mixed
            {
                return null;
            }

            public function valid(): bool
            {
                return false;
            }

            public function rewind(): void {}
        };

        $method = new \ReflectionMethod(NestedIterator::class, 'checkRewindable');

        self::assertTrue($method->invoke(new NestedIterator([]), $outer));
    }

    protected function createChunkSource(): \Closure
    {
        $items = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
            'f' => 'F',
        ];

        return function () use ($items) {
            foreach ($items as $key => $item) {
                yield $key => $item;
            }
        };
    }
}
