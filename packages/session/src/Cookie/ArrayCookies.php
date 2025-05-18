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

    protected array $valueOptions = [];

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

    public function set(string $name, string $value, ?array $options = null): bool
    {
        if (!in_array($name, $this->modifiedFields, true)) {
            $this->modifiedFields[] = $name;
        }

        $this->storage[$name] = $value;
        $this->valueOptions[$name] = $options;

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

        unset($this->storage[$name], $this->valueOptions[$name]);

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

            $options = $this->valueOptions[$k] ?? null;

            $isRemove = in_array($k, $this->removeFields, true);

            $header = $k . '=' . $item;

            if ($settings = $this->buildHeaderSettings($isRemove, $options)) {
                $header .= '; ' . $settings;
            }

            $headers[] = $header;
        }

        return $headers;
    }

    protected function buildHeaderSettings(bool $makeExpired = false, ?array $customOptions = null): string
    {
        $settings = [];

        $options = [
            ...$this->propertiesToOptions(),
            ...($customOptions ?? []),
        ];

        $options['expires'] = static::expiresToDatetime($options['expires'] ?? null);

        if ($options['domain']) {
            $settings[] = 'domain=' . $options['domain'];
        }

        if ($options['path']) {
            $settings[] = 'path=' . $options['path'];
        }

        if ($makeExpired) {
            $settings[] = 'Max-Age=0';
        } elseif ($options['expires']) {
            $settings[] = 'Expires=' . $options['expires']->format(\DateTimeInterface::COOKIE);
        }

        if ($options['secure']) {
            $settings[] = 'secure';
        }

        if ($options['samesite']) {
            $settings[] = 'SameSite=' . $options['samesite'];
        }

        if ($options['httponly']) {
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
