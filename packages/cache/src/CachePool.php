<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use DateTime;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use Windwalker\Cache\Exception\InvalidArgumentException;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\PhpFileSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Serializer\SerializerInterface;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\PhpFileStorage;
use Windwalker\Cache\Storage\StorageInterface;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * @psalm-type  CacheHandler = callable(CacheItemInterface): mixed
 */
class CachePool implements CachePoolInterface
{
    use LoggerAwareTrait;

    /** Prefix for per-item metadata sidecar records. */
    private const string ITEM_META_PREFIX = '--ww_item_meta--';

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

    public function __construct(
        protected StorageInterface $storage = new ArrayStorage(),
        protected SerializerInterface $serializer = new RawSerializer(),
        LoggerInterface $logger = new NullLogger(),
        protected DateInterval|int|null $defaultTtl = null,
    ) {
        $this->logger = $logger;
    }

    /**
     * Set the logger instance.
     *
     * @param  LoggerInterface  $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function withLogger(LoggerInterface $logger): static
    {
        $new = clone $this;
        $new->logger = $logger;

        return $new;
    }

    /**
     * @inheritDoc
     *
     * Note: This method does not actively delete expired or invalid items from storage.
     * It only marks them as cache misses. Item cleanup relies on:
     * - Storage-level expiration (TTL enforcement)
     * - Periodic pruning (e.g., ArrayStorage::prune())
     * - Explicit deleteItem() calls
     *
     * This approach keeps getItem() as a read-only operation, improving performance
     * and avoiding race conditions in concurrent environments.
     */
    public function getItem(string $key): CacheItem
    {
        $item = CacheItem::create($key);
        $item->setLogger($this->logger);
        $item->expiresAfter($this->getDefaultTtl());

        if (!$this->storage->has($key)) {
            return $item;
        }

        // Defensive re-check: some storages may flip from hit->miss between has() and get().
        $stored = $this->storage->get($key);
        if ($stored === null && !$this->storage->has($key)) {
            return $item;
        }

        $item->set($this->serializer->unserialize($stored));
        $this->hydrateItemMetadata($item);

        // If metadata says the item is already expired, do not serve stale data.
        if (!$item->isHit()) {
            return $item;
        }

        return $item;
    }

    /**
     * @inheritDoc
     *
     * @return iterable<CacheItem>
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
            $this->storage->remove($this->itemMetadataKey($key));

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

            $this->persistItemMetadata($item, $expiration);

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
    public function set($key, $value, $ttl = null, array $tags = []): bool
    {
        $item = $this->getItem($key);

        $item->expiresAfter($ttl ?? $this->defaultTtl);
        $item->set($value);

        if ($item instanceof CacheItem && $tags !== []) {
            $item->tags(...$tags);
        }

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
     * @param  CacheHandler           $handler  Invoked to compute the value on cache miss.
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

            // Determine whether the cached item is still usable.
            $isHit = $item->isHit();

            // Item is valid and does not need early recomputation: serve it.
            if ($isHit && !$this->shouldRecomputeEarly($item, $beta)) {
                return $item->get();
            }

            // Cache miss or probabilistic early expiry: recompute.
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

            // Keep Symfony-style metadata on supported CacheItem instances.
            if ($item instanceof CacheItem) {
                $item->setCtime($ctime);
            }

            $this->save($item);

            return $data;
        } finally {
            if ($locked) {
                CacheLock::release($key);
            }
        }
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
    protected function shouldRecomputeEarly(CacheItem $item, float $beta): bool
    {
        if ($beta <= 0.0) {
            return false;
        }

        if (is_infinite($beta) && $beta > 0.0) {
            return true; // INF → always recompute
        }

        $expiry = $item->realExpiry;
        $ctime = $item->ctime;

        if ($expiry <= 0.0) {
            $expiry = (float) $item->getExpiration()->format('U.u');
        }

        if ($expiry <= microtime(true)) {
            return true; // already expired
        }

        // Draw U ~ Uniform(0, 1] using CSPRNG for unbiased randomness.
        // -log(U) is Exponential(1), ranging from 0 (U=1) to +∞ (U→0).
        $u = random_int(1, PHP_INT_MAX) / PHP_INT_MAX;

        // Symfony-style ctime-aware early expiration. If ctime is missing, keep
        // the old remaining-TTL behavior as a backwards-compatible fallback.
        if ($ctime > 0) {
            return $expiry <= microtime(true) - ($ctime / 1000) * $beta * log($u);
        }

        $remainingTtl = $expiry - microtime(true);

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
     * @deprecated Use withStorage() instead.
     */
    public function setStorage(StorageInterface $storage): static
    {
        $this->storage = $storage;

        return $this;
    }

    public function withStorage(StorageInterface $storage): static
    {
        $new = clone $this;
        $new->storage = $storage;

        return $new;
    }

    /**
     * Return a new CachePool instance scoped to the given group.
     * Works only when the underlying storage implements GroupedStorageInterface;
     * throws LogicException otherwise.
     *
     * @throws \LogicException
     */
    public function withGroup(string $group): static
    {
        if (!$this->isGroupSupported()) {
            throw new \LogicException(
                sprintf(
                    'Storage class %s does not implement %s.',
                    get_class($this->storage),
                    GroupedStorageInterface::class,
                )
            );
        }

        $new = clone $this;
        $new->storage = $this->storage->withGroup($group);

        return $new;
    }

    /**
     * Method to get property Serializer
     *
     * @return  SerializerInterface
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
     * @deprecated Use withSerializer() instead.
     */
    public function setSerializer(SerializerInterface $serializer): static
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function withSerializer(SerializerInterface $serializer): static
    {
        $new = clone $this;
        $new->serializer = $serializer;

        return $new;
    }

    /**
     * Method to get property DeferredItems
     *
     * @return  array<CacheItem>
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
     * @deprecated Use withAutoCommit() instead.
     */
    public function autoCommit(bool $autoCommit): static
    {
        $this->autoCommit = $autoCommit;

        return $this;
    }

    public function withAutoCommit(bool $autoCommit): static
    {
        $new = clone $this;
        $new->autoCommit = $autoCommit;

        return $new;
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
     * Method to set property defaultTtl
     *
     * @param  DateInterval|int|null  $defaultTtl
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated Use withDefaultTtl() instead.
     */
    public function setDefaultTtl(DateInterval|int|null $defaultTtl): static
    {
        $this->defaultTtl = $defaultTtl;

        return $this;
    }

    public function withDefaultTtl(DateInterval|int|null $defaultTtl): static
    {
        $new = clone $this;
        $new->defaultTtl = $defaultTtl;


        return $new;
    }

    public function toTaggedPool(StorageInterface|CacheItemPoolInterface|null $tagPool): TaggedCachePool
    {
        $pool = new TaggedCachePool(
            $this->storage,
            $this->serializer,
            $this->logger,
            $this->defaultTtl,
            $tagPool,
        );

        return $pool->withAutoCommit($this->autoCommit);
    }

    /** Build the sidecar metadata key for a cache item key. */
    private function itemMetadataKey(string $key): string
    {
        return self::ITEM_META_PREFIX . hash('sha1', $key);
    }

    /** Persist fetch metadata in sidecar storage for later reads. */
    private function persistItemMetadata(CacheItem $item, int $expiration): void
    {
        $ctime = $item->ctime;
        $properties = [
            'realExpiry' => $item->realExpiry,
            'ctime' => $ctime,
        ];
        $metaKey = $this->itemMetadataKey($item->getKey());

        // Keep storage format backward-compatible for normal set()/save() values.
        if ($ctime <= 0) {
            $this->storage->remove($metaKey);

            return;
        }

        $value = serialize($properties);

        if ($this->storage instanceof PhpFileStorage) {
            $value = new PhpFileSerializer()->serialize($value);
        }

        $this->storage->save($metaKey, $value, $expiration);
    }

    /**
     * Hydrate fetch metadata sidecar back into CacheItem (if present).
     */
    private function hydrateItemMetadata(CacheItem $item): void
    {
        $metaKey = $this->itemMetadataKey($item->getKey());

        if (!$this->storage->has($metaKey)) {
            return;
        }

        $serialized = $this->storage->get($metaKey);

        if (!is_string($serialized) || $serialized === '') {
            return;
        }

        if ($this->storage instanceof PhpFileStorage) {
            $serialized = new PhpFileSerializer()->unserialize($serialized);
        }

        $properties = unserialize($serialized, ['allowed_classes' => false]);

        if (!is_array($properties)) {
            return;
        }

        $expiry = $properties['realExpiry'] ?? null;
        $ctime = $properties['ctime'] ?? 0;

        if (is_numeric($expiry)) {
            $item->setRealExpiry($expiry);
        }

        $item->setCtime((int) $ctime);

        $expiry = $item->realExpiry;
        $ctime = $item->ctime;

        if (is_numeric($expiry)) {
            $expiryString = number_format((float) $expiry, 6, '.', '');
            $expiryDate = DateTime::createFromFormat('U.u', $expiryString);

            if ($expiryDate !== false) {
                $item->expiresAt($expiryDate);
            }
        }

        $item->setCtime((int) $ctime);
    }

    /**
     * @return  bool
     */
    public function isGroupSupported(): bool
    {
        return $this->storage instanceof GroupedStorageInterface;
    }
}
