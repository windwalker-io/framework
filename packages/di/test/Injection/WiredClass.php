<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection;

use Windwalker\DI\Attributes\Autowire;
use Windwalker\Scalars\StringObject;

/**
 * The WiredClass class.
 */
#[Autowire]
class WiredClass
{
    public array $logs = [];

    /**
     * WiredClass constructor.
     *
     * @param  array              $logs
     * @param  StringObject|null  $foo
     */
    public function __construct(StringObject $foo, array $logs = [])
    {
        $this->logs[] = $foo;
    }
}
