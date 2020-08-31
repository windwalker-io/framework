<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Utilities\Arr;

use function Windwalker\arr;

/**
 * The ArrayModifyTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArrayModifyTraitTest extends TestCase
{
    protected $instance;

    public function testExcept(): void
    {
        $src = arr(
            [
                'ai' => 'Jarvis',
                'agent' => 'Phil Coulson',
                'green' => 'Hulk',
                'red' => [
                    'left' => 'Pepper',
                    'right' => 'Iron Man',
                ],
                'human' => [
                    'dark' => 'Nick Fury',
                    'black' => [
                        'male' => 'Loki',
                        'female' => 'Black Widow',
                        'no-gender' => 'empty',
                    ],
                ],
            ]
        );

        self::assertEquals(
            [
                'ai' => 'Jarvis',
                'agent' => 'Phil Coulson',
                'green' => 'Hulk',
            ],
            $src->except(['human', 'red'])->dump()
        );
    }

    public function testRemoveFirst(): void
    {
        $a = $this->instance->removeFirst();

        self::assertEquals([2, 3], $a->dump());

        $a = $this->instance->removeFirst(2);

        self::assertEquals([3], $a->dump());
    }

    public function testShuffle(): void
    {
        $this->instance = ArrayObject::range(1, 10);

        $a = $this->instance->shuffle();

        // Make sure shuffled
        self::assertNotEquals($this->instance->dump(), $a->dump());

        // Make sure all elements exists
        self::assertEquals($this->instance->intersect($a)->dump(), $this->instance->dump());
    }

    public function testReplaceRecursive(): void
    {
        $a = arr(
            $data = [
                'Jarvis',
                'Phil Coulson',
                'red' => [
                    'Pepper',
                    'Iron Man',
                ],
            ]
        );

        $b = arr(
            [
                'ai' => 'Jarvis',
                'agent' => 'Phil Coulson',
                'Hulk',
                'red' => [
                    'Nick Fury',
                    [
                        'male' => 'Loki',
                        'female' => 'Black Widow',
                        'no-gender' => 'empty',
                    ],
                ],
            ]
        );

        $a = $a->replaceRecursive($b);

        self::assertEquals(array_replace_recursive($a->dump(), $b->dump()), $a->dump());
    }

    public function testAppend(): void
    {
        self::assertEquals(
            [1, 2, 3, 10],
            $this->instance->append(10)->dump()
        );
    }

    public function testReverse(): void
    {
        self::assertEquals(
            [3, 2, 1],
            $this->instance->reverse()->dump()
        );

        self::assertEquals(
            [2 => 3, 1 => 2, 0 => 1],
            $this->instance->reverse(true)->dump()
        );
    }

    public function testInsertBefore(): void
    {
        self::assertEquals(
            [1, 'A', 2, 3],
            $this->instance->insertBefore(1, 'A')->dump()
        );
    }

    public function testKeyBy(): void
    {
        $src = arr(
            [
                [
                    'id' => 1,
                    'title' => 'Julius Caesar',
                    'data' => (object) ['foo' => 'bar'],
                ],
                [
                    'id' => 2,
                    'title' => 'Macbeth',
                    'data' => [],
                ],
                [
                    'id' => 3,
                    'title' => 'Othello',
                    'data' => 123,
                ],
                [
                    'id' => 4,
                    'title' => 'Hamlet',
                    'data' => true,
                ],
            ]
        );

        self::assertEquals(3, $src->keyBy('title')['Othello']['id']);
    }

    public function testLeftPad(): void
    {
        $a = $this->instance->leftPad(5, 'A');

        self::assertEquals(['A', 'A', 1, 2, 3], $a->dump());
    }

    public function testSlice(): void
    {
        $a = $this->instance->slice(0, 2);

        self::assertEquals([1, 2], $a->dump());
    }

    public function testPad(): void
    {
        $a = ArrayObject::explode(',', '1,2,3')->pad(5, 'A');

        self::assertEquals([1, 2, 3, 'A', 'A'], $a->dump());
    }

    public function testChunk(): void
    {
        $a = ArrayObject::range(1, 12)->chunk(4);

        self::assertEquals(
            [
                [1, 2, 3, 4],
                [5, 6, 7, 8],
                [9, 10, 11, 12],
            ],
            $a->dump(true)
        );

        self::assertInstanceOf(ArrayObject::class, $a[0]);
    }

    public function testGroupBy(): void
    {
        $src = arr(
            [
                [
                    'id' => 1,
                    'title' => 'Julius Caesar',
                    'group' => 'A',
                ],
                [
                    'id' => 2,
                    'title' => 'Macbeth',
                    'group' => 'B',
                ],
                [
                    'id' => 3,
                    'title' => 'Othello',
                    'group' => 'C',
                ],
                [
                    'id' => 4,
                    'title' => 'Hamlet',
                    'group' => 'D',
                ],
            ]
        );

        self::assertEquals(
            Arr::group($src->dump(), 'group'),
            $src->group('group')->dump(true)
        );
    }

    public function testTakeout(): void
    {
        $v = $this->instance->takeout(1);

        self::assertEquals(2, $v);
        self::assertEquals([0 => 1, 2 => 3], $this->instance->dump());
    }

    public function testRightPad(): void
    {
        $a = ArrayObject::explode(',', '1,2,3')->rightPad(5, 'A');

        self::assertEquals([1, 2, 3, 'A', 'A'], $a->dump());
    }

    public function testPush(): void
    {
        $r = $this->instance->push(5, 6);

        self::assertEquals(5, $r);

        self::assertEquals([1, 2, 3, 5, 6], $this->instance->dump());
    }

    public function testReplace(): void
    {
        $r = $this->instance->replace([1 => 'A', 0 => 'B']);

        self::assertEquals(['B', 'A', 3], $r->dump());
    }

    public function testPrepend(): void
    {
        self::assertEquals(['A', 'B', 1, 2, 3], $this->instance->prepend('A', 'B')->dump());
    }

    public function testShift(): void
    {
        $r = $this->instance->shift();

        self::assertEquals(1, $r);
        self::assertEquals([2, 3], $this->instance->dump());
    }

    public function testPop(): void
    {
        $r = $this->instance->pop();

        self::assertEquals(3, $r);
        self::assertEquals([1, 2], $this->instance->dump());
    }

    public function testRemoveLast(): void
    {
        $a = $this->instance->removeLast();

        self::assertEquals([1, 2], $a->dump());

        $a = $this->instance->removeLast(2);

        self::assertEquals([1], $a->dump());
    }

    public function testUnshift(): void
    {
        $r = $this->instance->unshift('A', 'B');

        self::assertEquals(5, $r);
        self::assertEquals(['A', 'B', 1, 2, 3], $this->instance->dump());
    }

    public function testSplice(): void
    {
        $r = $this->instance->splice(0, 2);

        self::assertEquals([1, 2], $r->dump());
        self::assertEquals([3], $this->instance->dump());
    }

    public function testInsertAfter(): void
    {
        $a = $this->instance->insertAfter(1, 'A');

        self::assertEquals([1, 2, 'A', 3], $a->dump());
    }

    public function testOnly(): void
    {
        $src = arr(
            [
                'ai' => 'Jarvis',
                'agent' => 'Phil Coulson',
                'green' => 'Hulk',
                'red' => [
                    'left' => 'Pepper',
                    'right' => 'Iron Man',
                ],
                'human' => [
                    'dark' => 'Nick Fury',
                    'black' => [
                        'male' => 'Loki',
                        'female' => 'Black Widow',
                        'no-gender' => 'empty',
                    ],
                ],
            ]
        );

        self::assertEquals(
            [
                'ai' => 'Jarvis',
                'agent' => 'Phil Coulson',
                'green' => 'Hulk',
            ],
            $src->only(['ai', 'agent', 'green'])->dump()
        );
    }

    /**
     * @see  ArrayObject::union
     */
    public function testUnion(): void
    {
        $a = arr(['a' => 'apple', 'b' => 'banana']);
        $b = arr(['a' => 'pear', 'b' => 'strawberry', 'c' => 'cherry']);

        self::assertEquals(
            [
                'a' => 'apple',
                'b' => 'banana',
                'c' => 'cherry',
            ],
            $a->union($b)->dump()
        );
    }

    protected function setUp(): void
    {
        $this->instance = arr([1, 2, 3]);
    }

    protected function tearDown(): void
    {
    }

    protected function getAssoc(): ArrayObject
    {
        return new ArrayObject(['foo' => 'bar', 'flower' => 'sakura']);
    }
}
