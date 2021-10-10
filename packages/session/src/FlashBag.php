<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session;

use Windwalker\Utilities\Cache\InstanceCacheTrait;

/**
 * The FlashBag class.
 */
class FlashBag
{
    use InstanceCacheTrait;

    /**
     * Since this property will be reference to session variable,
     * We must not declare type that to prevent reference type held error.
     *
     * This error often occurred by Symfony/VarDumper.
     *
     * @var ?array
     */
    protected mixed $storage = null;

    /**
     * FlashBag constructor.
     *
     * @param  array|null  $storage
     */
    public function __construct(?array $storage = [])
    {
        $this->storage = $storage;
    }

    public function link(array &$storage, string $name = '_flash'): static
    {
        $storage[$name] ??= [];
        $this->storage = &$storage[$name];

        return $this;
    }

    public function add(mixed $value, string $type = 'info'): void
    {
        $this->storage[$type] ??= [];

        $this->storage[$type][] = $value;
    }

    public function peek(?string $type = null): ?array
    {
        if ($type) {
            return $this->storage[$type] ?? null;
        }

        return $this->storage;
    }

    public function get(string $type): mixed
    {
        $msg = $this->storage[$type] ?? null;

        unset($this->storage[$type]);

        return $msg;
    }

    public function all(): ?array
    {
        return $this->once(
            'all',
            function () {
                $storage = $this->storage;

                $this->storage = [];

                return $storage;
            }
        );
    }

    /**
     * @return array|null
     */
    public function &getStorage(): ?array
    {
        return $this->storage;
    }

    /**
     * @param  array|null  $storage
     *
     * @return  static  Return self to support chaining.
     */
    public function setStorage(?array $storage): static
    {
        $this->storage = &$storage;

        return $this;
    }
}
