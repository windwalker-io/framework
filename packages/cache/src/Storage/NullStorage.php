<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * The NullStorage class.
 */
class NullStorage implements StorageInterface, GroupedStorageInterface
{
    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        return true;
    }

    public function withGroup(string $group): static
    {
        return clone $this;
    }
}
