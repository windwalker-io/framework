<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The NullCookies class.
 */
class NullCookies implements CookiesInterface
{
    /**
     * set
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return  bool
     */
    public function set(string $name, string $value): bool
    {
        return true;
    }

    /**
     * get
     *
     * @param  string  $name
     *
     * @return  string|null
     */
    public function get(string $name): ?string
    {
        return null;
    }
}
