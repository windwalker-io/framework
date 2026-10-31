<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;

use function Windwalker\arr;

/**
 * Tests the array access helpers of ArrayObject.
 */
class ArrayAccessTraitTest extends TestCase
{
    /**
     * @var  ArrayObject
     */
    protected $instance;

    #[Test]
    public function firstReturnsFirstItem(): void
    {
        self::assertEquals(1, $this->instance->first());
    }

    #[Test]
    public function firstReturnsFirstItemMatchingCallback(): void
    {
        self::assertEquals(
            4,
            $this->instance->first(
                function ($v, $k) {
                    return $k > 2;
                }
            )
        );
    }

    #[Test]
    public function lastReturnsLastItem(): void
    {
        self::assertEquals(5, $this->instance->last());
    }

    #[Test]
    public function lastReturnsLastItemMatchingCallback(): void
    {
        self::assertEquals(
            3,
            $this->instance->last(
                function ($v, $k) {
                    return $k < 3;
                }
            )
        );
    }

    #[Test]
    public function flattenJoinsNestedKeysWithDot(): void
    {
        $a = $this->createNestedArray();

        $this->assertEquals(
            [
                'flower' => 'sakura',
                'olive' => 'peace',
                'pos1.sunflower' => 'love',
                'pos2.cornflower' => 'elegant',
                'pos2.pos3.0' => 'olive',
            ],
            $a->flatten()->dump()
        );
    }

    #[Test]
    public function flattenWithCustomSeparatorDepthAndPrefix(): void
    {
        $a = $this->createNestedArray();

        $this->assertEquals(
            [
                'foo/flower' => 'sakura',
                'foo/olive' => 'peace',
                'foo/pos1/sunflower' => 'love',
                'foo/pos2/cornflower' => 'elegant',
                'foo/pos2/pos3' => ['olive'],
            ],
            $a->flatten('/', 2, 'foo')->dump()
        );
    }

    #[Test]
    public function collapseMergesNestedArraysIntoOneLevel(): void
    {
        $a = $this->createNestedArray();

        $this->assertEquals(
            [
                'flower' => 'sakura',
                'olive' => 'peace',
                'sunflower' => 'love',
                'cornflower' => 'elegant',
                '0' => 'olive',
            ],
            $a->collapse()->dump()
        );
    }

    #[Test]
    public function pageReturnsRequestedSlice(): void
    {
        $a = arr([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $a = $a->page(2, 3);

        self::assertEquals([4, 5, 6], $a->dump());
    }

    #[Test]
    public function firstReturnsNullForEmptyArray(): void
    {
        self::assertNull(arr([])->first());
    }

    #[Test]
    public function firstReturnsNullWhenNoItemMatchesCallback(): void
    {
        self::assertNull(arr([1, 2])->first(fn($v) => $v > 5));
    }

    #[Test]
    public function lastReturnsNullForEmptyArray(): void
    {
        self::assertNull(arr([])->last());
    }

    #[Test]
    public function lastReturnsNullWhenNoItemMatchesCallback(): void
    {
        self::assertNull(arr([1, 2])->last(fn($v) => $v > 5));
    }

    #[Test]
    public function firstReturnsNullWhenFirstValueIsNull(): void
    {
        self::assertNull(arr([null, 1])->first());
    }

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = arr([1, 2, 3, 4, 5]);
    }

    private function createNestedArray(): ArrayObject
    {
        return arr(
            [
                'flower' => 'sakura',
                'olive' => 'peace',
                'pos1' => [
                    'sunflower' => 'love',
                ],
                'pos2' => [
                    'cornflower' => 'elegant',
                    'pos3' => [
                        'olive',
                    ],
                ],
            ]
        );
    }
}
