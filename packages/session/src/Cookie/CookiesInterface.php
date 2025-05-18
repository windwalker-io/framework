<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * Interface CookiesInterface
 */
interface CookiesInterface
{
    public const SAMESITE_NONE = 'None';

    public const SAMESITE_LAX = 'Lax';

    public const SAMESITE_STRICT = 'Strict';

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
    public function set(string $name, string $value, ?array $options = null): bool;

    public function get(string $name): ?string;

    public function remove(string $name): bool;

    public function getStorage(): array;
}
