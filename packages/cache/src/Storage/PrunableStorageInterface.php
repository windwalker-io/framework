<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * Supports proactively pruning expired cache entries.
 */
interface PrunableStorageInterface
{
    /**
     * Remove expired entries from the current storage scope.
     *
     * @return int The number of pruned entries.
     */
    public function prune(): int;
}

