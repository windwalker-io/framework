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
 * The Wrapped class.
 */
class Wrapped
{
    public object $instance;

    /**
     * Wrapped constructor.
     *
     * @param  object  $instance
     */
    public function __construct(object $instance)
    {
        $this->instance = $instance;
    }
}
