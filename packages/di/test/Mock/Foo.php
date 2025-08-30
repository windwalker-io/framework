<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

use Windwalker\DI\Test\Stub\StubLazy;

/**
 * The Foo class.
 *
 * @since  2.0
 */
class Foo
{
    /**
     * Property bar.
     *
     * @var  Bar
     */
    public $bar = null;

    /**
     * Class init.
     *
     * @param  Bar  $bar
     */
    public function __construct(Bar $bar, public StubLazy $lazy)
    {
        $this->bar = $bar;
    }
}
