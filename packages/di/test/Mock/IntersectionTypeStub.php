<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

use Windwalker\Scalars\ArrayObject;

/**
 * The IntersectionTypeStub class.
 */
class IntersectionTypeStub
{
    public function __construct(public ArrayObject | (\Iterator & \Countable) $iter)
    {
    }
}
