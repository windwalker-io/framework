<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * The StorageInterface class.
 */
interface StorageInterface
{
    /**
     * @param  string  $key  The key to get value.
     *
     * @return  mixed
     */
    public function get(string $key): mixed;

    /**
     * @param  string  $key  The key to check value existent.
     *
     * @return  bool
     */
    public function has(string $key): bool;

    /**
     * @return bool
     */
    public function clear(): bool;

    /**
     * @param  string  $key  The key to be deleted.
     *
     * @return bool
     */
    public function remove(string $key): bool;

    /**
     * @param  string  $key         The key under which to store the value.
     * @param  mixed   $value       The value to store.
     * @param  int     $expiration  The expiration time, should be unix timestamp. Set to 0 will never expired.
     *
     * @return bool
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool;
}
