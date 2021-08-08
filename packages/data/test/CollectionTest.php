<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Data\Collection;

use function Windwalker\collect;

/**
 * The CollectionTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $instance;

    /**
     * @see  Collection::hasDeep
     */
    public function testHasDeep(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Collection::extract
     */
    public function testExtractSelf(): void
    {
        $extracted = $this->instance->extract();

        self::assertNotSame($extracted, $this->instance);
        self::assertEquals($extracted->dump(), $this->instance->dump());

        $new = $this->instance->setDeep('foo.bar.goo', 'Hola');

        self::assertNotEquals($extracted->get('foo.bar.goo'), $new->getDeep('foo.bar.goo'));

        // Proxy
        $extracted = $this->instance->extract(null, true);

        self::assertNotSame($extracted, $this->instance);
        self::assertEquals($extracted->dump(), $this->instance->dump());

        $new = $this->instance->setDeep('foo.bar.goo', 'Hola');

        self::assertEquals($extracted->getDeep('foo.bar.goo'), $new->getDeep('foo.bar.goo'));

        // Extract to non-array
        $this->expectExceptionMessage(
            sprintf(
                'Method: %s::extract() extract to sub element should be array, object or NULL,' .
                ' got string(4) "Hola".',
                Collection::class
            )
        );

        $extracted = $this->instance->extract('foo.bar.goo');
    }

    /**
     * @see  Collection::extract
     */
    public function testExtractDeep(): void
    {
        $extracted = $this->instance->extract('foo.bar');

        self::assertNotSame($extracted, $this->instance);
        self::assertEquals($extracted->dump(), $this->instance->getDeep('foo.bar'));

        $this->instance->setDeep('foo.bar.goo', 'Hola');

        self::assertNotEquals($extracted->get('goo'), $this->instance->getDeep('foo.bar.goo'));

        // Proxy
        $extracted = $this->instance->extract('foo.bar', true);

        self::assertNotSame($extracted, $this->instance);
        self::assertEquals($extracted->dump(), $this->instance->getDeep('foo.bar'));

        $extracted->setDeep('goo', 'Hola');

        self::assertEquals($extracted->getDeep('goo'), $this->instance->getDeep('foo.bar.goo'));

        // Proxy with some operation
        $extracted = $this->instance->extract('foo.bar.yoo.items', true);

        $extracted->push(
            [
                'id' => 4,
                'title' => 'Olive',
            ]
        );

        self::assertEquals(
            $extracted->dump(),
            $this->instance->getDeep('foo.bar.yoo.items')
        );

        // Extract to non-array
        $this->expectExceptionMessage(
            sprintf(
                'Method: %s::extract() Proxy to sub element should be array, got string(4) "Hola"',
                Collection::class
            )
        );

        $extracted = $this->instance->extract('foo.bar.goo', true);
    }

    public function testLoadFrom()
    {
        $data = Collection::from('{"foo": "bar"}');

        self::assertEquals(
            'bar',
            $data->foo
        );
    }

    /**
     * @see  Collection::proxy
     */
    public function testProxy(): void
    {
        $avg = $this->instance->proxy('foo.bar.yoo.items')->column('id')->sum();

        self::assertEquals(6, $avg);
    }

    /**
     * @see  Collection::removeDeep
     */
    public function testRemoveDeep(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Collection::getDeep
     */
    public function testGetDeep(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Collection::setDeep
     */
    public function testSetDeep(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = collect(
            [
                'foo' => [
                    'bar' => [
                        'goo' => 'Hello',
                        'yoo' => [
                            'uid' => 123456,
                            'items' => [
                                [
                                    'id' => 1,
                                    'title' => 'Sakura',
                                ],
                                [
                                    'id' => 2,
                                    'title' => 'Rose',
                                ],
                                [
                                    'id' => 3,
                                    'title' => 'Sunflower',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    protected function tearDown(): void
    {
    }
}
