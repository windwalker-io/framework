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
     * @param  string                     $name
     * @param  string                     $value
     * @param  CookiesOptions|array|null  $options
     *
     * @return  bool
     */
    public function set(string $name, string $value, CookiesOptions|array|null $options = null): bool
    {
        if (headers_sent()) {
            return false;
        }

        $options = CookiesOptions::wrapWith($options)->defaults($this->getOptions());

        return setcookie(
            $name,
            $value,
            $options->toCookieParams()
        );
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
