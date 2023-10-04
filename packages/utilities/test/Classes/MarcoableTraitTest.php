<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Classes;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Classes\MarcoableTrait;

/**
 * The MarcoableTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class MarcoableTraitTest extends TestCase
{
    /**
     * @var  MarcoableTrait
     */
    protected $instance;

    /**
     * Test __call
     *
     * @see  MarcoableTrait::__call
     */
    public function testCall(): void
    {
        $r = $this->instance->foo('Simon');

        self::assertEquals('Hello:Simon', $r);
    }

    /**
     * Test do
     *
     * @see  MarcoableTrait::do
     */
    public function testDo(): void
    {
        $r = $this->instance->do('foo', 'Simon');

        self::assertEquals('Hello:Simon', $r);
    }

    /**
     * Test hasMacro
     *
     * @see  MarcoableTrait::hasMacro
     */
    public function testHasMacro(): void
    {
        $r = $this->instance->do('foo', 'Simon');

        self::assertTrue($this->instance::hasMacro('foo'));
    }

    /**
     * Test __callStatic
     *
     * @see  MarcoableTrait::__callStatic
     */
    public function testCallStatic(): void
    {
        $r = $this->instance::foo('Simon');

        self::assertEquals('Hello:Simon', $r);
    }

    /**
     * Test
     *
     * @see  MarcoableTrait::clearMarco()
     */
    public function testClearMarco(): void
    {
        $this->instance::clearMarco();

        self::assertFalse($this->instance::hasMacro('foo'));
    }

    protected function setUp(): void
    {
        $this->instance = new class {
            use MarcoableTrait;
        };

        $this->instance::macro(
            'foo',
            function ($a = null) {
                return 'Hello:' . $a;
            }
        );
    }

    protected function tearDown(): void
    {
    }
}
