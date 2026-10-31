<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;

use function Windwalker\arr;

/**
 * Tests the array creation helpers of ArrayObject.
 */
class ArrayCreationTraitTest extends TestCase
{
    protected ArrayObject $instance;

    #[Test]
    public function combineUsesItemsAsKeysForGivenArray(): void
    {
        self::assertEquals(
            [
                1 => 'foo',
                2 => 'bar',
                3 => 'yoo',
            ],
            $this->instance->combine(['foo', 'bar', 'yoo'])->dump()
        );
    }

    #[Test]
    public function combineAcceptsArrayObject(): void
    {
        self::assertEquals(
            [
                1 => 'foo',
                2 => 'bar',
                3 => 'yoo',
            ],
            $this->instance->combine(arr(['foo', 'bar', 'yoo']))->dump()
        );
    }

    #[Test]
    public function mergeRecursiveMergesNestedArraysWithLaterValuesWinning(): void
    {
        $data1 = arr(
            [
                'green' => 'Hulk',
                'red' => 'empty',
                'human' => [
                    'dark' => 'empty',
                    'black' => [
                        'male' => 'empty',
                        'female' => 'empty',
                        'no-gender' => 'empty',
                    ],
                ],
            ]
        );

        $data2 = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'female' => 'Black Widow',
                    'male' => 'Loki',
                ],
            ],
        ];

        $data3 = arr(
            [
                'ai' => 'Ultron',
            ]
        );

        $expected = [
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
        ];

        $this->assertEquals($expected, $data1->mergeRecursive($data1, $data2)->dump());

        $expected['ai'] = 'Ultron';

        $this->assertEquals($expected, $data1->mergeRecursive($data2, $data3)->dump());
    }

    #[Test]
    public function mergeRecursiveThrowsOnNonArrayArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        arr(['foo' => 'bar'])->mergeRecursive('', 123);
    }

    #[Test]
    public function fillKeysFillsEachKeyWithValueAndReturnsNewInstance(): void
    {
        $a = $this->instance->fillKeys(['foo', 'bar'], [1, 2, 3]);

        self::assertEquals(
            [
                'foo' => [
                    0 => 1,
                    1 => 2,
                    2 => 3,
                ],
                'bar' => [
                    0 => 1,
                    1 => 2,
                    2 => 3,
                ],
            ],
            $a->dump()
        );
        self::assertNotSame($a, $this->instance);
    }

    #[Test]
    public function diffReturnsItemsMissingFromGivenArray(): void
    {
        $a = $this->instance->diff([3, 4, 5, 6]);

        self::assertEquals(
            [1, 2],
            $a->dump()
        );
        self::assertNotSame($a, $this->instance);
    }

    #[Test]
    public function mergeOverwritesKeysFromLaterArray(): void
    {
        $data1 = [
            'green' => 'Hulk',
            'red' => 'empty',
            'human' => [
                'dark' => 'empty',
                'black' => [
                    'male' => 'empty',
                    'female' => 'empty',
                    'no-gender' => 'empty',
                ],
            ],
        ];

        $data2 = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'female' => 'Black Widow',
                    'male' => 'Loki',
                ],
            ],
        ];

        self::assertEquals(
            array_merge($data1, $data2),
            arr($data1)->merge(arr($data2))->dump()
        );
    }

    #[Test]
    public function concatAppendsValuesOfGivenArray(): void
    {
        $data1 = [
            'green' => 'Hulk',
            'red' => 'empty',
            'human' => [
                'dark' => 'empty',
                'black' => [
                    'male' => 'empty',
                    'female' => 'empty',
                    'no-gender' => 'empty',
                ],
            ],
        ];

        $data2 = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'female' => 'Black Widow',
                    'male' => 'Loki',
                ],
            ],
        ];

        self::assertEquals(
            array_merge(array_values($data1), array_values($data2)),
            arr($data1)->concat(arr($data2))->dump()
        );
    }

    #[Test]
    public function concatStartPrependsValuesOfGivenArray(): void
    {
        $data1 = [
            'green' => 'Hulk',
            'red' => 'empty',
            'human' => [
                'dark' => 'empty',
            ],
        ];

        $data2 = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'red' => [
                'left' => 'Pepper',
            ],
            'human' => [
                'dark' => 'Nick Fury',
            ],
        ];

        self::assertEquals(
            array_merge(array_values($data2), array_values($data1)),
            arr($data1)->concatStart(arr($data2))->dump()
        );
    }

    #[Test]
    public function fillWithCreatesItemsFromStartIndex(): void
    {
        $a = ArrayObject::fillWith(5, 3, 'Y');

        self::assertEquals(
            [
                5 => 'Y',
                6 => 'Y',
                7 => 'Y',
            ],
            $a->dump()
        );
    }

    #[Test]
    public function intersectKeyKeepsItemsWithSharedKeys(): void
    {
        $a = $this->getAssoc();

        $b = arr(['flower' => 'Rose', 'animal' => 'bird']);

        self::assertEquals(['flower' => 'sakura'], $a->intersectKey($b)->dump());
    }

    #[Test]
    public function intersectKeepsItemsWithSharedValues(): void
    {
        $a = $this->getAssoc();

        $b = arr(['flower2' => 'sakura', 'animal' => 'bird']);

        self::assertEquals(['flower' => 'sakura'], $a->intersect($b)->dump());
    }

    #[Test]
    public function rangeCreatesSequentialItems(): void
    {
        $a = ArrayObject::range(1, 5);

        self::assertEquals(range(1, 5), $a->dump());
    }

    #[Test]
    public function flipSwapsKeysAndValues(): void
    {
        $a = $this->getAssoc();

        self::assertEquals(
            array_flip($a->dump()),
            $a->flip()->dump()
        );
    }

    #[Test]
    public function diffKeysReturnsItemsWithKeysMissingFromGivenArray(): void
    {
        $a = $this->getAssoc();

        $b = arr(['flower' => 'Rose', 'animal' => 'bird']);

        self::assertEquals(['foo' => 'bar'], $a->diffKeys($b)->dump());
    }

    #[Test]
    public function countValuesCountsOccurrencesOfEachValue(): void
    {
        $a = arr([1, 1, 2, 2, 2, 3, 3, 3, 3, 4, 5, 5]);

        self::assertEquals(
            [
                1 => 2,
                2 => 3,
                3 => 4,
                4 => 1,
                5 => 2,
            ],
            $a->countValues()->dump()
        );
    }

    #[Test]
    public function crossJoinBuildsCartesianProduct(): void
    {
        $a = arr([1, 2])->crossJoin(['a', 'b']);

        self::assertEquals(
            [
                [1, 'a'],
                [1, 'b'],
                [2, 'a'],
                [2, 'b'],
            ],
            $a->dump()
        );
    }

    #[Test]
    public function crossJoinSupportsMultipleArrays(): void
    {
        $b = arr([1, 2])->crossJoin(['a', 'b'], ['I', 'II']);

        self::assertEquals(
            [
                [1, 'a', 'I'],
                [1, 'a', 'II'],
                [1, 'b', 'I'],
                [1, 'b', 'II'],
                [2, 'a', 'I'],
                [2, 'a', 'II'],
                [2, 'b', 'I'],
                [2, 'b', 'II'],
            ],
            $b->dump()
        );
    }

    #[Test]
    public function randReturnsRandomExistingKeys(): void
    {
        $a = arr(['A', 'B', 'C', 'D', 'E']);

        $indexes = $a->rand(2);

        self::assertEquals($indexes->intersect($a->keys())->dump(), $indexes->dump());
    }

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = arr([1, 2, 3]);
    }

    protected function getAssoc(): ArrayObject
    {
        return new ArrayObject(['foo' => 'bar', 'flower' => 'sakura']);
    }
}
