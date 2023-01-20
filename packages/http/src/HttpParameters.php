<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http;

use Traversable;
use Windwalker\Utilities\Contract\AccessorAccessibleInterface;
use Windwalker\Utilities\Contract\ArrayAccessibleInterface;
use Windwalker\Utilities\TypeCast;

/**
 * A parameter bag class that store a set or data, and make getters/setters key insensitive.
 */
class HttpParameters implements AccessorAccessibleInterface, ArrayAccessibleInterface ,\IteratorAggregate
{
    protected array $storage = [];

    public static function wrap(array|self $data): static
    {
        if ($data instanceof self) {
            return $data;
        }

        return new static($data);
    }

    public function __construct(array $storage = [])
    {
        $this->storage = array_change_key_case($storage, CASE_LOWER);
    }

    public function &get(mixed $key): mixed
    {
        $key = strtolower($key);

        $ret = null;

        if (!isset($this->getStorage()[$key])) {
            return $ret;
        }

        $ret =& $this->getStorage()[$key];

        return $ret;
    }

    public function set(mixed $key, mixed $value): static
    {
        $key = strtolower($key);

        $this->storage[$key] = $value;

        return $this;
    }

    /**
     * Set value default if not exists.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function def(mixed $key, mixed $default): mixed
    {
        $key = strtolower($key);

        $this->getStorage()[$key] = $this->getStorage()[$key] ?? $default;

        return $this->getStorage()[$key];
    }

    /**
     * Check a key exists or not.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function has(mixed $key): bool
    {
        $key = strtolower($key);

        return isset($this->getStorage()[$key]);
    }

    /**
     * remove
     *
     * @param  mixed  $key
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function remove(mixed $key): static
    {
        $key = strtolower($key);

        if ($this->has($key)) {
            unset($this->getStorage()[$key]);
        }

        return $this;
    }

    /**
     * Creates a copy of storage.
     *
     * @param  bool  $recursive
     *
     * @param  bool  $onlyDumpable
     *
     * @return array
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        if (!$recursive) {
            return $this->getStorage();
        }

        $data = $this->getStorage();

        return TypeCast::toArray($data, true, $onlyDumpable);
    }

    public function offsetExists(mixed $key): bool
    {
        return $this->has($key);
    }

    public function &offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function offsetUnset(mixed $key): void
    {
        $this->remove($key);
    }

    public function &getStorage(): array
    {
        return $this->storage;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->storage);
    }
}
