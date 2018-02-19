<?php
/**
 * Part of windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DI\Test\Mock;

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
     * @param Bar $bar
     */
    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
}
