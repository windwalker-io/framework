<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

use DateTimeInterface;

/**
 * Interface ConfigurableCookiesInterface
 */
interface CookiesConfigurableInterface
{
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
