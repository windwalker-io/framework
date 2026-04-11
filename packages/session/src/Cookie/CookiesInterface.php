<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * Interface CookiesInterface
 */
interface CookiesInterface
{
    public const string SAMESITE_NONE = 'None';

    public const string SAMESITE_LAX = 'Lax';

    public const string SAMESITE_STRICT = 'Strict';

    /**
     * @param  string                     $name
     * @param  string                     $value
     * @param  CookiesOptions|array|null  $options
     *
     * @return  bool
     */
    public function set(string $name, string $value, CookiesOptions|array|null $options = null): bool;

    public function get(string $name): ?string;

    public function remove(string $name): bool;

    public function getStorage(): array;
}
