<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * The AbstractCookies class.
 */
abstract class AbstractConfigurableCookies implements CookiesInterface, CookiesConfigurableInterface
{
    protected ?DateTimeInterface $expires = null;

    protected string $path = '';

    protected string $domain = '';

    protected bool $secure = false;

    protected bool $httpOnly = false;

    protected string $sameSite = self::SAMESITE_LAX;

    /**
     * AbstractCookies constructor.
     *
     * @param  array  $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function getOptions(): array
    {
        return array_change_key_case(get_object_vars($this), CASE_LOWER);
    }

    public function setOptions(array $options): static
    {
        $this->path = $options['path'] ?? $this->path;
        $this->domain = $options['domain'] ?? $this->domain;
        $this->secure = $options['secure'] ?? $this->secure;
        $this->httpOnly = $options['httponly'] ?? $this->httpOnly;
        $this->sameSite = $options['samesite'] ?? $this->sameSite;

        $this->expires($options['expires'] ?? $this->expires);

        return $this;
    }

    /**
     * @return ?\DateTimeInterface
     */
    public function getExpires(): ?\DateTimeInterface
    {
        return $this->expires;
    }

    /**
     * @param  DateTimeInterface|int|string|null  $expires  Timestamp, DateTime or time modify string.
     *
     * @return static Return self to support chaining.
     * @throws \Exception
     */
    public function expires(int|string|DateTimeInterface|null $expires): static
    {
        if ($expires === null) {
            $this->expires = null;
            return $this;
        }

        if (!$expires instanceof DateTimeInterface) {
            $expires = new DateTimeImmutable($expires);
        }

        $this->expires = $expires;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function path(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param  string  $domain
     *
     * @return  static  Return self to support chaining.
     */
    public function domain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param  bool  $secure
     *
     * @return  static  Return self to support chaining.
     */
    public function secure(bool $secure): static
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param  bool  $httpOnly
     *
     * @return  static  Return self to support chaining.
     */
    public function httpOnly(bool $httpOnly): static
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * @return string
     */
    public function isSameSite(): string
    {
        return $this->sameSite;
    }

    /**
     * @param  string  $sameSite
     *
     * @return  static  Return self to support chaining.
     */
    public function sameSite(string $sameSite): static
    {
        $this->sameSite = $sameSite;

        return $this;
    }
}
