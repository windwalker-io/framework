<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * Optional capability interface for storages that support batch reads.
 *
 * Implementing this interface allows CachePool::getItems() to fetch multiple
 * values in a single I/O round-trip instead of one call per key.
 *
 * Contract: only found, non-expired keys are included in the returned array.
 * Keys that are missing or expired are simply absent (never null).
 */
interface MultiGetStorageInterface extends StorageInterface
{
    /**
     * Retrieve multiple values in a single operation.
     *
     * @param  string[]  $keys
     *
     * @return array<string, mixed>  Associative array of key → value for found items only.
     *                               Missing or expired keys are absent from the result.
     */
    public function getMultiple(array $keys): array;
}

