<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI {
    use Windwalker\DI\Definition\ObjectBuilderDefinition;

    if (!function_exists('create')) {
        function create(string|callable $class, ...$args): ObjectBuilderDefinition
        {
            return Container::define($class, $args);
        }
    }
}
