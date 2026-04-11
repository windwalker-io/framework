<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The ArrayCookie class.
 */
class ArrayCookies extends AbstractConfigurableCookies
{
    /**
     * @var array<string, ArrayCookieItem>
     */
    protected array $newStorage = [];

    protected array $storage = [];

    protected array $modifiedFields = [];

    protected array $valueOptions = [];

    protected array $removeFields = [];

    public static function create(array $storage = [], CookiesOptions|array $options = []): static
    {
        return new static($storage, $options);
    }

    /**
     * ArrayCookies constructor.
     *
     * @param  array  $storage
     * @param  CookiesOptions|array  $options
     */
    public function __construct(array $storage = [], CookiesOptions|array $options = [])
    {
        $options = CookiesOptions::wrapWith($options);

        $this->newStorage = array_map(
            fn($value) => new ArrayCookieItem($value),
            $storage
        );

        $this->storage = $storage;

        parent::__construct($options);
    }

    public function set(string $name, string $value, CookiesOptions|array|null $options = null): bool
    {
        $item = $this->newStorage[$name] ??= new ArrayCookieItem($value);

        $item->value = $value;
        $item->options = CookiesOptions::wrapWith($options)->defaults($this->getOptions());
        $item->modified = true;

        return true;
    }

    /**
     * get
     *
     * @param  string  $name
     *
     * @return  string|null
     */
    public function get(string $name): ?string
    {
        $item = $this->newStorage[$name] ?? null;

        if ($item?->deleted) {
            return null;
        }

        return $item?->value;
    }

    public function remove(string $name): bool
    {
        $item = $this->newStorage[$name] ?? null;

        if ($item) {
            $item->modified = true;
            $item->deleted = true;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getStorage(): array
    {
        return array_map(fn($item) => $item->value, $this->newStorage);
    }

    /**
     * @param  array  $storage
     *
     * @return  static  Return self to support chaining.
     */
    public function setStorage(array $storage): static
    {
        $this->newStorage = array_map(
            fn($value) => new ArrayCookieItem($value),
            $storage
        );

        return $this;
    }

    public function getCookieHeaders(): array
    {
        $headers = [];

        foreach ($this->newStorage as $k => $item) {
            $options = $item->options->defaults($this->getOptions());

            if ($item->deleted) {
                $header = $k . '=""';

                if ($settings = $this->buildHeaderSettings(true, $options)) {
                    $header .= '; ' . $settings;
                }

                $headers[] = $header;

                continue;
            }

            if (!$item->modified) {
                continue;
            }

            $header = $k . '=' . $item->value;

            if ($settings = $this->buildHeaderSettings(false, $options)) {
                $header .= '; ' . $settings;
            }

            $headers[] = $header;
        }

        return $headers;
    }

    protected function buildHeaderSettings(
        bool $makeExpired = false,
        CookiesOptions $customOptions = new CookiesOptions()
    ): string {
        $settings = [];

        $options = $customOptions->defaults($this->getOptions());

        // $expires = static::expiresToDatetime($options->expires);

        if ($options->domain) {
            $settings[] = 'domain=' . $options->domain;
        }

        if ($options->path) {
            $settings[] = 'path=' . $options->path;
        }

        if ($makeExpired) {
            $settings[] = 'Max-Age=0';
        } elseif ($options->expires) {
            $settings[] = 'Expires=' . $options->expires->format(\DateTimeInterface::COOKIE);
        }

        if ($options->secure) {
            $settings[] = 'secure';
        }

        if ($options->sameSite) {
            $settings[] = 'SameSite=' . $options->sameSite;
        }

        if ($options->httpOnly) {
            $settings[] = 'HttpOnly';
        }

        return implode('; ', $settings);
    }

    /**
     * @return array
     */
    public function getModifiedFields(): array
    {
        return $this->modifiedFields;
    }

    /**
     * @param  array  $modifiedFields
     *
     * @return  static  Return self to support chaining.
     */
    public function setModifiedFields(array $modifiedFields): static
    {
        $this->modifiedFields = $modifiedFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getRemoveFields(): array
    {
        return $this->removeFields;
    }

    /**
     * @param  array  $removeFields
     *
     * @return  static  Return self to support chaining.
     */
    public function setRemoveFields(array $removeFields): static
    {
        $this->removeFields = $removeFields;

        return $this;
    }
}
