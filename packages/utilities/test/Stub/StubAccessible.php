<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

namespace Windwalker\Utilities\Test\Stub;

use Windwalker\Utilities\Accessible\AccessibleTrait;
use Windwalker\Utilities\Contract\AccessibleInterface;

/**
 * The StubAccessible class.
 *
 * @since  __DEPLOY_VERSION__
 */
class StubAccessible implements AccessibleInterface
{
    use AccessibleTrait;

    /**
     * StubAccessible constructor.
     *
     * @param  array  $storage
     */
    public function __construct(array $storage = [])
    {
        $this->storage = $storage;
    }
}
