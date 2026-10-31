<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;

use function Windwalker\arr;
use function Windwalker\value_compare;

/**
 * Tests the sorting helpers of ArrayObject.
 */
class ArraySortTraitTest extends TestCase
{
    protected ArrayObject $instance;

    #[Test]
    public function sortByFieldNameOrdersItemsAndPreservesKeys(): void
    {
        $a = $this->createPlays()->sortBy('id');

        self::assertEquals(
            ['Julius Caesar', 'Macbeth', 'Othello', 'Hamlet'],
            $a->column('title')->dump()
        );

        self::assertEquals(
            [3, 1, 0, 2],
            $a->keys()->dump()
        );
    }

    #[Test]
    public function sortByCallableOrdersItemsByReturnedValue(): void
    {
        $a = $this->createPlays()->sortBy(
            function ($item) {
                return $item['id'];
            }
        );

        self::assertEquals(
            ['Julius Caesar', 'Macbeth', 'Othello', 'Hamlet'],
            $a->column('title')->dump()
        );

        self::assertEquals(
            [3, 1, 0, 2],
            $a->keys()->dump()
        );
    }

    #[Test]
    public function sortByCallableReceivesKey(): void
    {
        $a = $this->createPlays()->sortBy(
            function ($item, $k) {
                return $k;
            }
        );

        self::assertEquals(
            [0, 1, 2, 3],
            $a->keys()->dump()
        );
    }

    #[Test]
    public function sortOrdersValuesAscendingAndPreservesKeys(): void
    {
        $a = $this->instance->sort();

        self::assertSame(
            [
                3 => 'A',
                5 => 'B',
                1 => 'H',
                2 => 'Z',
            ],
            $a->dump()
        );
    }

    #[Test]
    public function sortWithCallbackOrdersValuesUsingCallback(): void
    {
        $r = $this->instance->sort(
            function ($a, $b) {
                return strcmp($a, $b);
            }
        );

        self::assertSame(
            [
                3 => 'A',
                5 => 'B',
                1 => 'H',
                2 => 'Z',
            ],
            $r->dump()
        );
    }

    #[Test]
    public function sortKeysDescOrdersByKeyDescending(): void
    {
        $a = $this->instance->sortKeysDesc();

        self::assertSame(
            [
                5 => 'B',
                3 => 'A',
                2 => 'Z',
                1 => 'H',
            ],
            $a->dump()
        );
    }

    #[Test]
    public function natureSortKeysOrdersNumericKeysNaturally(): void
    {
        $a = arr(['img12' => 1, 'img10' => 2, 'img2' => 3])->natureSortKeys();

        self::assertEquals(['img2', 'img10', 'img12'], array_keys($a->dump()));
    }

    #[Test]
    public function natureSortOrdersNumbersInStringsNaturally(): void
    {
        $a = arr(['img12.png', 'img10.png', 'img2.png', 'img1.png']);

        $a = $a->natureSort();

        self::assertSame(
            [
                3 => 'img1.png',
                2 => 'img2.png',
                1 => 'img10.png',
                0 => 'img12.png',
            ],
            $a->dump()
        );
    }

    #[Test]
    public function sortKeysOrdersByKeyAscending(): void
    {
        $a = $this->instance->sortKeys();

        self::assertSame(
            [
                1 => 'H',
                2 => 'Z',
                3 => 'A',
                5 => 'B',
            ],
            $a->dump()
        );
    }

    #[Test]
    public function sortKeysWithCallbackOrdersKeysUsingCallback(): void
    {
        $r = $this->instance->sortKeys(static fn($a, $b) => value_compare($a, $b));

        self::assertEquals(
            [
                1 => 'H',
                2 => 'Z',
                3 => 'A',
                5 => 'B',
            ],
            $r->dump()
        );
    }

    #[Test]
    public function natureSortCaseInsensitiveIgnoresLetterCase(): void
    {
        $a = arr(['IMG0.png', 'img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG3.png']);

        $a = $a->natureSortCaseInsensitive();

        self::assertSame(
            [
                0 => 'IMG0.png',
                4 => 'img1.png',
                3 => 'img2.png',
                5 => 'IMG3.png',
                2 => 'img10.png',
                1 => 'img12.png',
            ],
            $a->dump()
        );
    }

    #[Test]
    public function sortDescOrdersValuesDescending(): void
    {
        $a = $this->instance->sortDesc();

        self::assertSame(
            [
                2 => 'Z',
                1 => 'H',
                5 => 'B',
                3 => 'A',
            ],
            $a->dump()
        );
    }

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = arr(
            [
                '3' => 'A',
                '2' => 'Z',
                '5' => 'B',
                '1' => 'H',
            ]
        );
    }

    private function createPlays(): ArrayObject
    {
        return arr(
            [
                [
                    'id' => 3,
                    'title' => 'Othello',
                    'data' => 123,
                ],
                [
                    'id' => 2,
                    'title' => 'Macbeth',
                    'data' => [],
                ],
                [
                    'id' => 4,
                    'title' => 'Hamlet',
                    'data' => true,
                ],
                [
                    'id' => 1,
                    'title' => 'Julius Caesar',
                    'data' => (object) ['foo' => 'bar'],
                ],
            ]
        );
    }
}
