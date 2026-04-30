<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * Supports selecting cache group scope dynamically.
 */
interface GroupedStorageInterface
{
    public function withGroup(string $group): static;
}

