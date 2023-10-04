<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

/**
 * The Env class.
 */
class Env
{
    /**
     * Get env.
     *
     * @param  string      $name
     * @param  mixed|null  $default
     *
     * @return  string|null
     */
    public static function get(string $name, mixed $default = null): ?string
    {
        return $_SERVER[$name] ?? $_ENV[$name] ?? $default;
    }
}
