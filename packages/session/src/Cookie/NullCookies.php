<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The NullCookies class.
 */
class NullCookies implements CookiesInterface
{
    public function set(string $name, string $value, ?array $options = null): bool
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

    public function remove(string $name): bool
    {
        return true;
    }

    public function getStorage(): array
    {
        return [];
    }
}
