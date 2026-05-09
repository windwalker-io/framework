<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Windwalker\Cache\Storage\StorageInterface;

interface TaggedCachePoolInterface extends CachePoolInterface
{
    public function invalidateTags(string ...$tags): bool;

    public function getTagPool(): CacheItemPoolInterface|false;

    public function withTagPool(StorageInterface|CacheItemPoolInterface|null|false $tagPool): static;

    public function getKnownTagVersionsTtl(): float;

    public function withKnownTagVersionsTtl(float $knownTagVersionsTtl): static;

    public function withoutKnownTagVersionsCache(): static;

    public function isItemValid(CacheItemInterface $item): bool;
}

