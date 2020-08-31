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
use Windwalker\Scalars\Concern\ArraySortTrait;

use function Windwalker\arr;
use function Windwalker\value_compare;

/**
 * The ArraySortTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArraySortTraitTest extends TestCase
{
    protected $instance;

    /**
     * Test sortColumn
     *
     * @see  ArraySortTrait::sortBy
     */
    public function testSortBy(): void
    {
        $data = arr(
            $src = [
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

        $a = $data->sortBy('id');

        self::assertEquals(
            ['Julius Caesar', 'Macbeth', 'Othello', 'Hamlet'],
            $a->column('title')->dump()
        );

        self::assertEquals(
            [3, 1, 0, 2],
            $a->keys()->dump()
        );

        // Test callable
        $a = $data->sortBy(
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

        $a = $data->sortBy(
            function ($item, $k) {
                return $k;
            }
        );

        self::assertEquals(
            [0, 1, 2, 3],
            $a->keys()->dump()
        );
    }

    /**
     * Test asort
     *
     * @see  ArraySortTrait::sort
     */
    public function testSort(): void
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

    /**
     * Test krsort
     *
     * @see  ArraySortTrait::sortKeysDesc
     */
    public function testSortKeysDesc(): void
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

    /**
     * Test natsort
     *
     * @see  ArraySortTrait::natureSort
     */
    public function testNatsort(): void
    {
        $a = arr($src = ['img12.png', 'img10.png', 'img2.png', 'img1.png']);

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

    /**
     * Test ksort
     *
     * @see  ArraySortTrait::sortKeys
     */
    public function testSortKeys(): void
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

    /**
     * Test natcasesort
     *
     * @see  ArraySortTrait::natureSortCaseInsensitive
     */
    public function testNatcasesort(): void
    {
        $a = arr($src = ['IMG0.png', 'img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG3.png']);

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

    public function testSortDesc(): void
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

    protected function setUp(): void
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

    protected function tearDown(): void
    {
    }
}
