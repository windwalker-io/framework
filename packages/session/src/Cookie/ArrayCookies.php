<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The ArrayCookie class.
 */
class ArrayCookies extends AbstractConfigurableCookies
{
    protected array $storage = [];

    protected array $modifiedFields = [];

    protected array $removeFields = [];

    public static function create(array $storage = [], array $options = []): static
    {
        return new static($storage, $options);
    }

    /**
     * ArrayCookies constructor.
     *
     * @param  array  $storage
     * @param  array  $options
     */
    public function __construct(array $storage = [], array $options = [])
    {
        $this->storage = $storage;

        parent::__construct($options);
    }

    /**
     * set
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return  bool
     */
    public function set(string $name, string $value): bool
    {
        if (!in_array($name, $this->modifiedFields, true)) {
            $this->modifiedFields[] = $name;
        }

        $this->storage[$name] = $value;

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
        return $this->storage[$name] ?? null;
    }

    public function remove(string $name): bool
    {
        if (!in_array($name, $this->modifiedFields, true)) {
            $this->modifiedFields[] = $name;
        }

        if (!in_array($name, $this->removeFields, true)) {
            $this->removeFields[] = $name;
        }

        unset($this->storage[$name]);

        return true;
    }

    /**
     * @return array
     */
    public function getStorage(): array
    {
        return $this->storage;
    }

    /**
     * @param  array  $storage
     *
     * @return  static  Return self to support chaining.
     */
    public function setStorage(array $storage): static
    {
        $this->storage = $storage;

        return $this;
    }

    public function getCookieHeaders(): array
    {
        $headers = [];

        foreach ($this->getStorage() as $k => $item) {
            if (!in_array($k, $this->modifiedFields, true)) {
                continue;
            }

            $isRemove = in_array($k, $this->removeFields, true);

            $header = $k . '=' . $item;

            if ($settings = $this->buildHeaderSettings($isRemove)) {
                $header .= '; ' . $settings;
            }

            $headers[] = $header;
        }

        return $headers;
    }

    protected function buildHeaderSettings(bool $makeExpired = false): string
    {
        $settings = [];

        if ($this->domain) {
            $settings[] = 'domain=' . $this->domain;
        }

        if ($this->path) {
            $settings[] = 'path=' . $this->path;
        }

        if ($makeExpired) {
            $settings[] = 'Max-Age=0';
        } elseif ($this->expires) {
            $settings[] = 'Expires=' . $this->expires->format(\DateTimeInterface::COOKIE);
        }

        if ($this->secure) {
            $settings[] = 'secure';
        }

        if ($this->sameSite) {
            $settings[] = 'SameSite=' . $this->sameSite;
        }

        if ($this->httpOnly) {
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
