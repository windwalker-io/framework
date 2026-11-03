<?php

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

    /**
     * Set env.
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return  void
     */
    public static function set(string $name, string $value): void
    {
        $_SERVER[$name] = $value;
        $_ENV[$name] = $value;

        putenv($name . '=' . $value);
    }
}
