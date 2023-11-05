<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use PHPUnit\Framework\TestCase;
use Windwalker\Scalars\ArrayObject;

use function Windwalker\arr;

/**
 * The ArrayContentTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArrayAccessTraitTest extends TestCase
{
    /**
     * @var  ArrayObject
     */
    protected $instance;

    public function testFirst(): void
    {
        self::assertEquals(1, $this->instance->first());
        self::assertEquals(
            4,
            $this->instance->first(
                function ($v, $k) {
                    return $k > 2;
                }
            )
        );
    }

    public function testLast(): void
    {
        self::assertEquals(5, $this->instance->last());
        self::assertEquals(
            3,
            $this->instance->last(
                function ($v, $k) {
                    return $k < 3;
                }
            )
        );
    }

    public function testFlatten(): void
    {
        $a = arr(
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

    public function testCollapse(): void
    {
        $a = arr(
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

    /**
     * @see  ArrayAccessTrait::page
     */
    public function testPage(): void
    {
        $a = arr([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $a = $a->page(2, 3);

        self::assertEquals([4, 5, 6], $a->dump());
    }

    protected function setUp(): void
    {
        $this->instance = arr([1, 2, 3, 4, 5]);
    }
}
