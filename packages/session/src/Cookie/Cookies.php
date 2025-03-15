<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The CookieSetter class.
 */
class Cookies extends AbstractConfigurableCookies
{
    public static function create(): static
    {
        return new static();
    }

    /**
     * @param  string      $name
     * @param  string      $value
     * @param  ?array{
     *      expires?: int,
     *      path?: string,
     *      domain?: string,
     *      secure?: bool,
     *      httponly?: bool,
     *      samesite?: "Lax"|"Strict"|"None"
     *  }  $options
     *
     * @return  bool
     */
    public function set(string $name, string $value, ?array $options = null): bool
    {
        if (headers_sent()) {
            return false;
        }

        return setcookie($name, $value, $options ?? $this->getOptions());
    }

    public function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    public function remove(string $name): bool
    {
        return setcookie($name, '', ['expires' => time() - 500]);
    }

    public function getStorage(): array
    {
        return $_COOKIE;
    }
}
