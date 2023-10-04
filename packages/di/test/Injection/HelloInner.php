<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection;

/**
 * The HelloInner class.
 */
#[HelloWrapper('World')]
class HelloInner
{
    /**
     * HelloInner constructor.
     *
     * @param  mixed  $bar
     */
    public function __construct(public $bar)
    {
    }
}
