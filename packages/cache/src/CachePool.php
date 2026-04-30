<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Throwable;
use Traversable;
use Windwalker\Cache\Exception\InvalidArgumentException;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Serializer\SerializerInterface;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\StorageInterface;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * The Pool class.
 */
class CachePool implements CacheItemPoolInterface, CacheInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Key prefix for tag-version entries.
     * Uses uncommon prefix to minimize collision with user-defined keys.
     */
    private const string TAG_VER_PREFIX = '__ww_tag_ver__';

    /**
     * Key prefix for per-item tag-envelope entries.
     * Uses uncommon prefix to minimize collision with user-defined keys.
     */
    private const string TAG_ENV_PREFIX = '__ww_tag_env__';


    /**
     * @var bool
     */
    protected bool $commiting = false;

    /**
     * @var array
     */
    protected array $deferredItems = [];

    /**
     * @var bool
     */
    private bool $autoCommit = true;

    /**
     * @var CacheItemPoolInterface|null
     */
    protected CacheItemPoolInterface|null $tagPool = null;


    public function __construct(
        protected StorageInterface $storage = new ArrayStorage(),
        protected SerializerInterface $serializer = new RawSerializer(),
        LoggerInterface $logger = new NullLogger(),
        protected DateInterval|int|null $defaultTtl = null,
        CacheItemPoolInterface|StorageInterface|null $tagPool = null,
    ) {
        $this->logger = $logger;
        $this->setTagPool($tagPool);
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        $item = CacheItem::create($key);
        $item->setLogger($this->logger);
        $item->expiresAfter($this->getDefaultTtl());

        if (!$this->storage->has($key)) {
            return $item;
        }

        return $item->set($this->serializer->unserialize($this->storage->get($key)));
    }

    /**
     * @inheritDoc
     *
     * @return Traversable|CacheItemInterface[]
     */
    public function getItems(iterable $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        try {
            $this->storage->clear();

            return true;
        } catch (RuntimeException $e) {
            $this->logException(
                'Clearing cache pool caused exception.',
                $e
            );

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        try {
            $this->storage->remove($key);

            return true;
        } catch (RuntimeException $e) {
            $this->logException(
                'Deleting cache item caused exception.',
                $e
            );

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $results = true;

        foreach ($keys as $key) {
            $results = $this->deleteItem($key) && $results;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        try {
            if (!$item instanceof CacheItem) {
                throw new InvalidArgumentException('Only support ' . CacheItem::class);
            }

            $expiration = $item->getExpiration()->getTimestamp();

            if ($expiration < time()) {
                $this->deleteItem($item->getKey());
                $item->setIsHit(false);

                return false;
            }

            $this->storage->save(
                $item->getKey(),
                $this->serializer->serialize($item->get()),
                $expiration
            );

            return true;
        } catch (RuntimeException $e) {
            $this->logException(
                'Saving cache item caused exception.',
                $e,
                $item
            );

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            throw new InvalidArgumentException('Only support ' . CacheItem::class);
        }

        while ($this->commiting) {
            usleep(1);
        }

        $this->deferredItems[$item->getKey()] = $item;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $this->commiting = true;

        foreach ($this->deferredItems as $key => $item) {
            if ($this->save($item)) {
                unset($this->deferredItems[$key]);
            }
        }

        $this->commiting = false;

        return !count($this->deferredItems);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null): mixed
    {
        $item = $this->getItem($key);

        if (!$item->isHit()) {
            return $default;
        }

        return $item->get();
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        $item = $this->getItem($key);

        $item->expiresAfter($ttl ?? $this->defaultTtl);
        $item->set($value);

        return $this->save($item);
    }

    /**
     * @inheritDoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete($key): bool
    {
        return $this->deleteItem($key);
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null): iterable
    {
        foreach ($this->getItems($keys) as $key => $item) {
            yield $key => $item->isHit() ? $item->get() : $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        ArgumentsAssert::assert(
            is_iterable($values),
            '{caller} values must be iterable, %s given.',
            $values
        );

        $results = true;

        foreach ($values as $key => $value) {
            $results = $this->set($key, $value, $ttl) && $results;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        ArgumentsAssert::assert(
            is_iterable($keys),
            '{caller} keys must be iterable, %s given.',
            $keys
        );

        $results = true;

        foreach ($keys as $key) {
            $results = $this->delete($key) && $results;
        }

        return $results;
    }

    /**
     * @inheritDoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function has($key): bool
    {
        return $this->hasItem($key);
    }

    /**
     * Fetch a value from cache, computing and storing it if missing or due for early recomputation.
     *
     * @param  string                 $key
     * @param  callable               $handler  Invoked to compute the value on cache miss.
     *                                          Receives the CacheItem as first argument.
     *                                          May call $item->tag('foo', 'bar') to associate tags.
     * @param  null|int|DateInterval  $ttl
     * @param  float                  $beta     XFetch beta factor.
     *                                          0   = no early expiration.
     *                                          1.0 = default (recommended).
     *                                          INF = always recompute (bypass cache).
     * @param  bool                   $lock     Whether to acquire a CacheLock (default true).
     *                                          Disable for in-process caches (e.g. ArrayStorage)
     *                                          or single-threaded environments to avoid flock overhead.
     *
     * @return  mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function fetch(
        string $key,
        callable $handler,
        DateInterval|int|null $ttl = null,
        float $beta = 1.0,
        bool $lock = true,
    ): mixed {
        $locked = $lock && CacheLock::lock($key, $isNew);

        try {
            // Re-fetch after acquiring the lock so we see any value a competing
            // process may have written while we were waiting.
            $item = $this->getItem($key);

            // Re-entrant call: this process already holds the stripe lock higher
            // in the call stack — return the current cached value as-is.
            if ($locked && !$isNew) {
                return $item->get();
            }

            // Determine whether the cached item is still usable:
            // - basic hit check
            // - tag validity (any invalidated tag makes the item stale)
            $isHit = $item->isHit();

            if ($isHit) {
                $storedTags = $this->getItemTags($key);

                if ($storedTags !== [] && !$this->isTagValid($key, $storedTags)) {
                    $isHit = false;
                }
            }

            // Item is valid and does not need early recomputation: serve it.
            if ($isHit && !$this->shouldRecomputeEarly($item, $beta)) {
                return $item->get();
            }

            // Cache miss, tag-invalidated, or probabilistic early expiry: recompute.
            $item->expiresAfter($ttl);

            $data = $handler($item);

            if (!$data instanceof CacheItemInterface) {
                $item->set($data);
            } else {
                $item = $data;
                $data = $item->get();
            }


            $this->save($item);

            // Save the tag envelope so future reads can detect invalidation.
            $tags = $item->getTags();
            if ($tags !== []) {
                $this->saveTagEnvelope($key, $tags, $item->getExpiration()->getTimestamp());
            }

            return $data;
        } finally {
            if ($locked) {
                CacheLock::release($key);
            }
        }
    }

    /**
     * Invalidate all cache entries associated with any of the given tags.
     *
     * Works by bumping the version token for each tag.  Any cached item whose
     * stored tag-version no longer matches the current version is considered
     * stale and will be recomputed on the next fetch().
     *
     * @param  string[]  $tags
     */
    public function invalidateTags(array $tags): bool
    {
        foreach ($tags as $tag) {
            $key = $this->tagVersionKey($tag);
            $version = $this->generateTagVersion();

            if ($this->tagPool !== null) {
                // Use CacheItemPoolInterface::getItem/save
                $item = $this->tagPool->getItem($key);
                $item->set($version);
                $item->expiresAfter(null); // null means forever
                $this->tagPool->save($item);
            } else {
                // Use own storage - 0 means no expiration
                $this->storage->save($key, $version, 0);
            }
        }

        return true;
    }

    // -----------------------------------------------------------------------
    // Tag helpers (private)
    // -----------------------------------------------------------------------

    /**
     * Storage key for a tag's version token.
     * Uses uncommon prefix to minimize collision with user-defined keys.
     */
    private function tagVersionKey(string $tag): string
    {
        return self::TAG_VER_PREFIX . hash('sha1', $tag);
    }

    /**
     * Storage key for a cache-item's tag envelope.
     * Uses uncommon prefix to minimize collision with user-defined keys.
     */
    private function tagEnvelopeKey(string $cacheKey): string
    {
        return self::TAG_ENV_PREFIX . hash('sha1', $cacheKey);
    }

    /** Generate a random, opaque tag-version token. */
    private function generateTagVersion(): string
    {
        return bin2hex(random_bytes(8));
    }

    /**
     * Return the current version token for each requested tag.
     * Tags that have never been explicitly invalidated have an empty-string version.
     *
     * @param  string[]  $tags
     * @return array<string, string>  tag => version
     */
    private function getCurrentTagVersions(array $tags): array
    {
        $versions = [];

        foreach ($tags as $tag) {
            $key = $this->tagVersionKey($tag);

            if ($this->tagPool !== null) {
                // Use CacheItemPoolInterface::getItem
                $item = $this->tagPool->getItem($key);
                $versions[$tag] = $item->isHit() ? (string) $item->get() : '';
            } else {
                // Use own storage
                $versions[$tag] = $this->storage->has($key)
                    ? (string) $this->storage->get($key)
                    : '';
            }
        }

        return $versions;
    }

    /**
     * Persist the tag envelope for a newly-computed cache entry.
     * The envelope records the current version of every tag so that future
     * reads can detect whether any tag has been invalidated since.
     *
     * @param  string[]  $tags
     */
    private function saveTagEnvelope(string $key, array $tags, int $expiration): void
    {
        $envKey = $this->tagEnvelopeKey($key);
        $envelope = serialize($this->getCurrentTagVersions($tags));

        if ($this->tagPool !== null) {
            // Use CacheItemPoolInterface::getItem/save with calculated TTL
            $ttl = max(0, $expiration - time());
            $item = $this->tagPool->getItem($envKey);
            $item->set($envelope);
            $item->expiresAfter($ttl > 0 ? $ttl : null);
            $this->tagPool->save($item);
        } else {
            // Use own storage
            $this->storage->save($envKey, $envelope, $expiration);
        }
    }

    /**
     * Check whether a cached item is still valid with respect to its tags.
     *
     * Returns false when:
     *  - no tag envelope was stored (item pre-dates tag-awareness, treat as stale)
     *  - any tag version in the envelope differs from the current storage version
     *
     * @param  string[]  $tags
     */
    private function isTagValid(string $key, array $tags): bool
    {
        $envKey = $this->tagEnvelopeKey($key);

        if ($this->tagPool !== null) {
            // Use CacheItemPoolInterface::getItem
            $item = $this->tagPool->getItem($envKey);

            if (!$item->isHit()) {
                return false;
            }

            $storedVersions = unserialize((string) $item->get());
        } else {
            // Use own storage
            if (!$this->storage->has($envKey)) {
                return false;
            }

            $storedVersions = unserialize((string) $this->storage->get($envKey));
        }

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

    /**
     * Retrieve the tags that were previously stored with a cache item.
     * Returns empty array if the item has no tag envelope.
     *
     * @return string[]
     */
    private function getItemTags(string $key): array
    {
        $envKey = $this->tagEnvelopeKey($key);

        if ($this->tagPool !== null) {
            // Use CacheItemPoolInterface::getItem
            $item = $this->tagPool->getItem($envKey);

            if (!$item->isHit()) {
                return [];
            }

            $storedVersions = unserialize((string) $item->get());
        } else {
            // Use own storage
            if (!$this->storage->has($envKey)) {
                return [];
            }

            $storedVersions = unserialize((string) $this->storage->get($envKey));
        }

        if (!is_array($storedVersions)) {
            return [];
        }

        return array_keys($storedVersions);
    }

    /**
     * @deprecated Use fetch() instead.
     */
    public function call(
        string $key,
        callable $handler,
        DateInterval|int|null $ttl = null,
        bool $lock = false,
    ): mixed {
        return $this->fetch($key, $handler, $ttl, 1.0, $lock);
    }

    /**
     * XFetch probabilistic early-expiration check.
     *
     * Returns true when the cached item should be recomputed before it
     * actually expires, to avoid a thundering-herd at expiry time.
     *
     * Formula: recompute when  remaining_ttl < beta * (-ln U)
     *          where U ~ Uniform(0, 1].
     *
     * @see https://www.vldb.org/pvldb/vol8/p886-vattani.pdf
     */
    private function shouldRecomputeEarly(CacheItem $item, float $beta): bool
    {
        if ($beta <= 0.0) {
            return false;
        }

        if (is_infinite($beta) && $beta > 0.0) {
            return true; // INF → always recompute
        }

        $expiry        = (float) $item->getExpiration()->getTimestamp();
        $remainingTtl  = $expiry - microtime(true);

        if ($remainingTtl <= 0.0) {
            return true; // already expired
        }

        // Draw U ~ Uniform(0, 1] using CSPRNG for unbiased randomness.
        // -log(U) is Exponential(1), ranging from 0 (U=1) to +∞ (U→0).
        $u = random_int(1, PHP_INT_MAX) / PHP_INT_MAX;

        return $remainingTtl < $beta * (-log($u));
    }

    /**
     * logException
     *
     * @param  string                   $message
     * @param  Throwable                $e
     * @param  CacheItemInterface|null  $item
     *
     * @return  void
     */
    protected function logException(string $message, Throwable $e, ?CacheItemInterface $item = null): void
    {
        $this->logger->critical(
            $message,
            [
                'exception' => $e,
                'key' => $item?->getKey(),
            ]
        );
    }

    /**
     * Method to get property Storage
     *
     * @return  StorageInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Method to set property storage
     *
     * @param  StorageInterface  $storage
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setStorage(StorageInterface $storage): static
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Method to get property Serializer
     *
     * @return  SerializerInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * Method to set property serializer
     *
     * @param  SerializerInterface  $serializer
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setSerializer(SerializerInterface $serializer): static
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Method to get property DeferredItems
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDeferredItems(): array
    {
        return $this->deferredItems;
    }

    /**
     * Method to set property autoCommit
     *
     * @param  bool  $autoCommit
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function autoCommit(bool $autoCommit): static
    {
        $this->autoCommit = $autoCommit;

        return $this;
    }

    /**
     * Commit when destructing.
     */
    public function __destruct()
    {
        if ($this->autoCommit) {
            $this->commit();
        }
    }

    public function getDefaultTtl(): DateInterval|int|null
    {
        return $this->defaultTtl;
    }

    /**
     * @param  DateInterval|int|null  $defaultTtl
     *
     * @return  static  Return self to support chaining.
     */
    public function setDefaultTtl(DateInterval|int|null $defaultTtl): static
    {
        $this->defaultTtl = $defaultTtl;

        return $this;
    }

    public function getTagPool(): CacheItemPoolInterface|null
    {
        return $this->tagPool;
    }

    public function setTagPool(StorageInterface|CacheItemPoolInterface|null $tagPool): static
    {
        if ($tagPool instanceof StorageInterface) {
            $tagPool = new CachePool($tagPool);
        }

        $this->tagPool = $tagPool;


        return $this;
    }
}
