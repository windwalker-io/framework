<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\PhpFileSerializer;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Serializer\SerializerInterface;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\PhpFileStorage;
use Windwalker\Cache\Storage\StorageInterface;

class TaggedCachePool extends CachePool implements TaggedCachePoolInterface
{
    private const string TAG_VER_PREFIX = '--ww_tag_ver--';

    private const string TAG_ENV_PREFIX = '--ww_tag_env--';

    protected CacheItemPoolInterface $tagPool;

    /** @var array<string, array{float, string}> */
    private array $knownTagVersions = [];

    private float $knownTagVersionsTtl = 0.15;

    public function __construct(
        StorageInterface $storage = new ArrayStorage(),
        SerializerInterface $serializer = new RawSerializer(),
        LoggerInterface $logger = new NullLogger(),
        DateInterval|int|null $defaultTtl = null,
        CacheItemPoolInterface|StorageInterface|null $tagPool = null,
    ) {
        parent::__construct($storage, $serializer, $logger, $defaultTtl);
        $this->applyTagPool($tagPool);
    }

    public function getItem(string $key): CacheItem
    {
        $item = parent::getItem($key);

        if (!$item->isHit()) {
            return $item;
        }

        if (!$this->isItemTagsValid($key)) {
            $item->setIsHit(false);
        }

        return $item;
    }

    public function deleteItem(string $key): bool
    {
        $result = parent::deleteItem($key);

        if (!$result) {
            return $result;
        }

        return $this->tagPool->deleteItem($this->tagEnvelopeKey($key));
    }

    public function save(CacheItemInterface $item): bool
    {
        if (!parent::save($item)) {
            return false;
        }

        if (!$item instanceof CacheItem) {
            return true;
        }

        try {
            $this->saveTagEnvelope($item->getKey(), $item->getTags(), $item->getExpiration()->getTimestamp());
        } catch (RuntimeException $e) {
            $this->logException('Saving cache item caused exception.', $e, $item);

            return false;
        }

        return true;
    }

    /** @psalm-param callable(CacheItem): mixed $handler */
    public function fetch(
        string $key,
        callable $handler,
        DateInterval|int|null $ttl = null,
        float $beta = 1.0,
        bool $lock = true,
    ): mixed {
        $locked = $lock && CacheLock::lock($key, $isNew, $this->logger);

        try {
            $item = $this->getItem($key);

            if ($locked && !$isNew) {
                return $item->get();
            }

            $isHit = $item->isHit() && $this->isItemTagsValid($key);

            if ($isHit && !$this->shouldRecomputeEarly($item, $beta)) {
                return $item->get();
            }

            $item->expiresAfter($ttl);

            $start = microtime(true);
            $data = $handler($item);
            $ctime = max(1, (int) round(max(0.0, microtime(true) - $start) * 1000));

            if (!$data instanceof CacheItemInterface) {
                $item->set($data);
            } else {
                $item = $data;
                $data = $item->get();
            }

            if ($item instanceof CacheItem) {
                $item->setCtime($ctime);
            }

            $this->save($item);

            return $data;
        } finally {
            if ($locked) {
                CacheLock::release($key, $this->logger);
            }
        }
    }

    public function invalidateTags(string ...$tags): bool
    {
        foreach ($tags as $tag) {
            unset($this->knownTagVersions[$tag]);
            $this->tagPool->deleteItem($this->tagVersionKey($tag));
        }

        return true;
    }

    public function isItemValid(CacheItemInterface $item): bool
    {
        if (!$item->isHit()) {
            return false;
        }

        if (!$this->storage->has($item->getKey())) {
            return false;
        }

        return $this->isItemTagsValid($item->getKey());
    }

    public function withGroup(string $group): static
    {
        $new = parent::withGroup($group);

        if (
            $new->tagPool instanceof CachePoolInterface
            && $new->tagPool->isGroupSupported()
        ) {
            $new->tagPool = $new->tagPool->withGroup($group);
        }

        return $new;
    }

    public function getTagPool(): CacheItemPoolInterface
    {
        return $this->tagPool;
    }

    public function withTagPool(StorageInterface|CacheItemPoolInterface|null $tagPool): static
    {
        $new = clone $this;
        $new->applyTagPool($tagPool);

        return $new;
    }

    public function getKnownTagVersionsTtl(): float
    {
        return $this->knownTagVersionsTtl;
    }

    public function withKnownTagVersionsTtl(float $knownTagVersionsTtl): static
    {
        $new = clone $this;
        $new->knownTagVersionsTtl = max(0, $knownTagVersionsTtl);

        return $new;
    }

    public function withoutKnownTagVersionsCache(): static
    {
        $new = clone $this;
        $new->knownTagVersions = [];

        return $new;
    }

    private function applyTagPool(StorageInterface|CacheItemPoolInterface|null $tagPool): void
    {
        if ($tagPool instanceof CacheItemPoolInterface) {
            $pool = $tagPool;
        } else {
            $storage = $tagPool instanceof StorageInterface ? $tagPool : $this->storage;

            $serializer = new PhpSerializer();

            if ($storage instanceof PhpFileStorage) {
                $serializer = new PhpFileSerializer();
            }

            $pool = new CachePool($storage, $serializer, $this->logger, null);
        }

        $this->tagPool = $pool;
    }

    private function tagVersionKey(string $tag): string
    {
        return self::TAG_VER_PREFIX . hash('sha1', $tag);
    }

    private function tagEnvelopeKey(string $cacheKey): string
    {
        return self::TAG_ENV_PREFIX . hash('sha1', $cacheKey);
    }

    private function generateTagVersion(): string
    {
        return bin2hex(random_bytes(8));
    }

    /** @param  string[]  $tags
     * @return array<string, string>
     */
    private function getCurrentTagVersions(array $tags): array
    {
        if (!$tags) {
            return [];
        }

        $versions = [];
        $now = microtime(true);
        $tagsToFetch = [];

        foreach ($tags as $tag) {
            if ($this->knownTagVersionsTtl > 0 && isset($this->knownTagVersions[$tag])) {
                [$expiration, $cachedVersion] = $this->knownTagVersions[$tag];

                if ($now <= $expiration) {
                    $versions[$tag] = $cachedVersion;
                    continue;
                }
            }

            $tagsToFetch[] = $tag;
        }

        if ($tagsToFetch) {
            $expiration = $now + $this->knownTagVersionsTtl;

            foreach ($tagsToFetch as $tag) {
                $item = $this->tagPool->getItem($this->tagVersionKey($tag));
                $version = $item->isHit() ? (string) $item->get() : '';

                $versions[$tag] = $version;

                if ($this->knownTagVersionsTtl > 0) {
                    unset($this->knownTagVersions[$tag]);
                    $this->knownTagVersions[$tag] = [$expiration, $version];
                }
            }

            if ($this->knownTagVersionsTtl > 0) {
                foreach ($this->knownTagVersions as $tag => [$exp, $ver]) {
                    if ($now > $exp) {
                        unset($this->knownTagVersions[$tag]);
                    } else {
                        break;
                    }
                }
            }
        }

        return $versions;
    }

    /** @param  string[]  $tags */
    private function saveTagEnvelope(string $key, array $tags, int $expiration): void
    {
        $envelope = $this->getOrCreateTagVersions($tags);

        $ttl = max(0, $expiration - time());
        $item = $this->tagPool->getItem($this->tagEnvelopeKey($key));
        $item->set($envelope);
        $item->expiresAfter($ttl > 0 ? $ttl : null);
        $this->tagPool->save($item);
    }

    /** @param  string[]  $tags
     * @return array<string, string>
     */
    private function getOrCreateTagVersions(array $tags): array
    {
        $versions = [];
        $now = microtime(true);

        foreach ($tags as $tag) {
            if ($this->knownTagVersionsTtl > 0 && isset($this->knownTagVersions[$tag])) {
                [$exp, $v] = $this->knownTagVersions[$tag];

                if ($now <= $exp && $v !== '') {
                    $versions[$tag] = $v;
                    continue;
                }
            }

            $tagItem = $this->tagPool->getItem($this->tagVersionKey($tag));
            $version = $tagItem->isHit() ? (string) $tagItem->get() : '';

            if ($version === '') {
                $version = $this->generateTagVersion();
                $tagItem->set($version);
                $tagItem->expiresAfter(null);
                $this->tagPool->save($tagItem);
            }

            $versions[$tag] = $version;

            if ($this->knownTagVersionsTtl > 0) {
                unset($this->knownTagVersions[$tag]);
                $this->knownTagVersions[$tag] = [$now + $this->knownTagVersionsTtl, $version];
            }
        }

        return $versions;
    }

    /** @param  string[]  $tags */
    private function isTagValid(string $key, array $tags): bool
    {
        $item = $this->tagPool->getItem($this->tagEnvelopeKey($key));

        if (!$item->isHit()) {
            return false;
        }

        $storedVersions = $item->get();

        if (!is_array($storedVersions)) {
            return false;
        }

        $currentVersions = $this->getCurrentTagVersions($tags);

        foreach ($tags as $tag) {
            if (($storedVersions[$tag] ?? null) !== ($currentVersions[$tag] ?? '')) {
                return false;
            }
        }

        return true;
    }

    private function isItemTagsValid(string $key): bool
    {
        $envelopeItem = $this->tagPool->getItem($this->tagEnvelopeKey($key));

        if (!$envelopeItem->isHit()) {
            return false;
        }

        $storedVersions = $envelopeItem->get();

        if (!is_array($storedVersions)) {
            return false;
        }

        if ($storedVersions === []) {
            return true;
        }

        return $this->isTagValid($key, array_keys($storedVersions));
    }
}
