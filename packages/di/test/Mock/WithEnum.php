<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

class WithEnum
{
    public function __construct(public FooEnum $foo)
    {
    }
}
