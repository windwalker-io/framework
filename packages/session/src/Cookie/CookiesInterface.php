<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

use DateTimeInterface;

/**
 * Interface CookiesInterface
 */
interface CookiesInterface
{
    public const SAMESITE_NONE = 'None';

    public const SAMESITE_LAX = 'Lax';

    public const SAMESITE_STRICT = 'Strict';

    public function set(string $name, string $value): bool;

    public function get(string $name): ?string;

    public function remove(string $name): bool;

    public function getStorage(): array;

    /**
     * @param  DateTimeInterface|int|string  $expires  Timestamp, DateTime or time modify string.
     *
     * @return static Return self to support chaining.
     * @throws \Exception
     */
    public function expires(int|string|DateTimeInterface $expires): static;

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function path(string $path): static;

    /**
     * @param  string  $domain
     *
     * @return  static  Return self to support chaining.
     */
    public function domain(string $domain): static;

    /**
     * @param  bool  $secure
     *
     * @return  static  Return self to support chaining.
     */
    public function secure(bool $secure): static;

    /**
     * @param  bool  $httpOnly
     *
     * @return  static  Return self to support chaining.
     */
    public function httpOnly(bool $httpOnly): static;

    /**
     * @param  string  $sameSite
     *
     * @return  static  Return self to support chaining.
     */
    public function sameSite(string $sameSite): static;
}
