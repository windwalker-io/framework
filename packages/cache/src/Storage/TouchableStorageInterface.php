<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

interface TouchableStorageInterface
{
    /**
     * Update the expiration time of a cache item, the implementation should be atomic to avoid race conditions.
     *
     * If the driver does not support atomic update, do not implement this interface.
     *
     * @param  string  $key         The key of the cache item.
     * @param  int     $expiration  The new expiration time, should be unix timestamp. Set to 0 will never expired.
     *
     * @return bool Only return true if the expiration time was updated successfully, false otherwise.
     *              Note: If item exists but the expiration time was not updated, also return false.
     *              Do not use return value to check if the item exists.
     */
    public function updateExpiration(string $key, int $expiration = 0): bool;
}
