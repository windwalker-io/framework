<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Mock;

use Windwalker\Session\Cookie\Cookies;
use Windwalker\Session\Cookie\CookiesOptions;

/**
 * The MockCookieSetter class.
 */
class MockCookies extends Cookies
{
    public array $cookies = [];

    public array $cookieData = [];

    public function set(string $name, string $value, CookiesOptions|array|null $options = null): bool
    {
        $this->cookies[$name] = $value;
        $options = CookiesOptions::wrapWith($options)->defaults($this->getOptions());

        $opt = $options->toCookieParams();
        $opt['value'] = $value;
        $this->cookieData[$name] = $opt;

        return true;
    }

    public function clear(): void
    {
        $this->cookies = [];
        $this->cookieData = [];
    }
}
