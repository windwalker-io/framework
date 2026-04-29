<?php

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

    protected CookiesOptions $options;

    /**
     * AbstractCookies constructor.
     *
     * @param  CookiesOptions|array|null  $options
     */
    public function __construct(CookiesOptions|array $options = new CookiesOptions())
    {
        $options = CookiesOptions::wrapWith($options);
        $options->sameSite ??= CookiesInterface::SAMESITE_LAX;

        $this->setOptions($options);
    }

    public function getOptions(): CookiesOptions
    {

        // if (isset($options['expires']) && $options['expires'] instanceof DateTimeInterface) {
        //     $options['expires'] = time() + $options['expires']->getTimestamp();
        // }

        return $this->options;
    }

    // protected function propertiesToOptions(): array
    // {
    //     return array_change_key_case(get_object_vars($this), CASE_LOWER);
    // }

    public function setOptions(CookiesOptions|array $options): static
    {
        $options = $options instanceof CookiesOptions ? $options : CookiesOptions::wrapWith($options);

        $this->options = $options;

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
        $this->expires = static::expiresToDatetime($expires);

        return $this;
    }

    public static function expiresToDatetime(mixed $expires): ?DateTimeInterface
    {
        if ($expires === null) {
            return null;
        }

        if (is_int($expires)) {
            $expires += time();
            $expires = DateTimeImmutable::createFromFormat('U', (string) $expires);
        }

        if (!$expires instanceof DateTimeInterface) {
            $expires = new DateTimeImmutable($expires);
        }

        return $expires;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->options->path ?? '';
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function path(string $path): static
    {
        $this->options->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->options->domain ?? '';
    }

    /**
     * @param  string  $domain
     *
     * @return  static  Return self to support chaining.
     */
    public function domain(string $domain): static
    {
        $this->options->domain = $domain;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->options->secure ?? false;
    }

    /**
     * @param  bool  $secure
     *
     * @return  static  Return self to support chaining.
     */
    public function secure(bool $secure): static
    {
        $this->options->secure = $secure;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->options->httpOnly ?? false;
    }

    /**
     * @param  bool  $httpOnly
     *
     * @return  static  Return self to support chaining.
     */
    public function httpOnly(bool $httpOnly): static
    {
        $this->options->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * @return string
     */
    public function isSameSite(): string
    {
        return $this->options->sameSite ?? '';
    }

    /**
     * @param  string  $sameSite
     *
     * @return  static  Return self to support chaining.
     */
    public function sameSite(string $sameSite): static
    {
        $this->options->sameSite = $sameSite;

        return $this;
    }
}
