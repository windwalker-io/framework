<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

class ArrayCookieItem
{
    public function __construct(
        public string $value,
        public bool $modified = false,
        public bool $deleted = false,
        public CookiesOptions $options = new CookiesOptions(),
    ) {
    }
}
