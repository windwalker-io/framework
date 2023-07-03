<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Context\Loop;
use Windwalker\Utilities\Iterator\NestedIterator;

/**
 * The MultiLevelIteratorTest class.
 */
class NestedIteratorTest extends TestCase
{
    /**
     * @var NestedIterator
     */
    protected $instance;

    /**
     * @see  NestedIterator::__construct
     */
    public function testNestedWrap(): void
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

    public function testEach(): void
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

    public function testMap(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->map('strtoupper');

        self::assertEquals(
            ['A', 'B', 'C', 'D', 'E', 'F'],
            iterator_to_array($iter)
        );
    }

    public function testFlatMap(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->flatMap(
            fn ($v) => [strtoupper($v), $v]
        );

        self::assertEquals(
            ['A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e', 'F', 'f'],
            iterator_to_array($iter, false)
        );
    }

    public function testMapWithKey(): void
    {
        $iter = new NestedIterator(['a', 'b', 'c', 'd', 'e', 'f']);
        $iter = $iter->mapWithKey(fn($item) => [$item => strtoupper($item)]);

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

    public function testFilter()
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

    public function testRewind()
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

    public function testRewindGenerator(): void
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

    public function testRewindCallback(): void
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

    public function testChunk()
    {
        $items = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
            'f' => 'F',
        ];

        $gen = function () use ($items) {
            foreach ($items as $key => $item) {
                yield $key => $item;
            }
        };

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

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
