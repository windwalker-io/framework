<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

interface LockableStorageInterface
{
    public function lock(string $key, ?bool &$isNew = null): bool;

    public function release(string $key): bool;

    public function locking(\Closure $handler, bool $enabled = true): mixed;

    public function isLocked(string $key): bool;
}
