<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Contract;

/**
 * Interface AccessorAccessibleInterface
 */
interface AccessorAccessibleInterface
{
    /**
     * Get value from this object.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function &get(mixed $key): mixed;

    /**
     * Set value to this object.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return  static
     */
    public function set(mixed $key, mixed $value): static;

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
    public function def(mixed $key, mixed $default): mixed;

    /**
     * Check a key exists or not.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function has(mixed $key): bool;

    /**
     * remove
     *
     * @param  mixed  $key
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function remove(mixed $key): static;

    /**
     * Creates a copy of storage.
     *
     * @param  bool  $recursive
     *
     * @param  bool  $onlyDumpable
     *
     * @return array
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array;
}
