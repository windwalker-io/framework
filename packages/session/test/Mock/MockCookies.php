<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Mock;

use Windwalker\Session\Cookie\Cookies;

/**
 * The MockCookieSetter class.
 */
class MockCookies extends Cookies
{
    public array $cookies = [];

    public array $cookieData = [];

    public function set(string $name, string $value, ?array $options = null): bool
    {
        $this->cookies[$name] = $value;

        $opt = [
            ...$this->getOptions(),
            ...($options ?? [])
        ];
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
