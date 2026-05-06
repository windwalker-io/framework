<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use DateTime;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Throwable;
use Windwalker\Cache\Exception\InvalidArgumentException;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\PhpFileSerializer;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Serializer\SerializerInterface;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\PhpFileStorage;
use Windwalker\Cache\Storage\StorageInterface;
use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * @psalm-type  CacheHandler = callable(CacheItemInterface): mixed
 */
class CachePool implements CacheItemPoolInterface, CacheInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Key prefix for tag-version entries.
     * Uses uncommon prefix to minimize collision with user-defined keys.
     */
    private const string TAG_VER_PREFIX = '--ww_tag_ver--';

    /**
     * Key prefix for per-item tag-envelope entries.
     * Uses uncommon prefix to minimize collision with user-defined keys.
     */
    private const string TAG_ENV_PREFIX = '--ww_tag_env--';

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

    protected CacheItemPoolInterface|false $tagPool;

    /**
     * In-memory cache of tag versions to reduce storage reads within a request.
     * Format: [tag => [expiration_microtime, version_string]]
     *
     * This cache is cleared on invalidateTags() and expires after knownTagVersionsTtl.
     *
     * @var array<string, array{float, string}>
     */
    private array $knownTagVersions = [];

    /**
     * TTL for in-memory tag version cache (in seconds).
     * Default 0.15 seconds (150ms) - enough to optimize multiple fetches within a single request.
     * Set to 0 to disable the cache.
     *
     * @var float
     */
    private float $knownTagVersionsTtl = 0.15;

    public function __construct(
        protected StorageInterface $storage = new ArrayStorage(),
        protected SerializerInterface $serializer = new RawSerializer(),
        LoggerInterface $logger = new NullLogger(),
        protected DateInterval|int|null $defaultTtl = null,
        CacheItemPoolInterface|StorageInterface|null|false $tagPool = null,
    ) {
        $this->logger = $logger;
        $this->applyTagPool($tagPool);
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
            $this->deleteItem($key);
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

            if ($this->tagPool !== false) {
                $this->tagPool->deleteItem($this->tagEnvelopeKey($key));
            }

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

            if ($this->tagPool !== false) {
                $tags = $item->getTags();

                if ($tags !== []) {
                    $this->saveTagEnvelope($item->getKey(), $tags, $expiration);
                } else {
                    $this->tagPool->deleteItem($this->tagEnvelopeKey($item->getKey()));
                }
            }

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
        $locked = $lock && CacheLock::lock($key, $isNew, $this->logger);

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

            // Only check tags if tagPool is enabled (not false)
            if ($isHit && $this->tagPool !== false) {
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
                CacheLock::release($key, $this->logger);
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
     * Note: If tagPool is set to false (tags disabled), this method is a no-op.
     */
    public function invalidateTags(string ...$tags): bool
    {
        // If tags are disabled, do nothing
        if ($this->tagPool === false) {
            return true;
        }

        foreach ($tags as $tag) {
            // Clear in-memory cache for this tag
            unset($this->knownTagVersions[$tag]);

            // Delete the version key — no need to write a new value.
            // Items whose envelopes stored the old (non-empty) version will be
            // treated as stale because '' !== <old_version>.
            $this->tagPool->deleteItem($this->tagVersionKey($tag));
        }

        return true;
    }

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
     * Uses in-memory cache (knownTagVersions) to reduce storage reads within a request.
     *
     * @param  string[]  $tags
     *
     * @return array<string, string>  tag => version
     */
    private function getCurrentTagVersions(array $tags): array
    {
        if (!$tags) {
            return [];
        }

        $versions = [];
        $now = microtime(true);
        $tagsToFetch = [];

        // Check in-memory cache first
        foreach ($tags as $tag) {
            if ($this->knownTagVersionsTtl > 0 && isset($this->knownTagVersions[$tag])) {
                [$expiration, $cachedVersion] = $this->knownTagVersions[$tag];

                if ($now <= $expiration) {
                    // Cache hit and not expired
                    $versions[$tag] = $cachedVersion;
                    continue;
                }
            }

            // Cache miss or expired or disabled - need to fetch
            $tagsToFetch[] = $tag;
        }

        // Fetch uncached tags from storage
        if ($tagsToFetch) {
            $expiration = $now + $this->knownTagVersionsTtl;

            foreach ($tagsToFetch as $tag) {
                $key = $this->tagVersionKey($tag);

                // Use CacheItemPoolInterface::getItem
                $item = $this->tagPool->getItem($key);
                $version = $item->isHit() ? (string) $item->get() : '';

                $versions[$tag] = $version;

                // Update in-memory cache if enabled
                if ($this->knownTagVersionsTtl > 0) {
                    // FIFO: remove first to re-add at end
                    unset($this->knownTagVersions[$tag]);
                    $this->knownTagVersions[$tag] = [$expiration, $version];
                }
            }

            // Clean expired entries from in-memory cache
            if ($this->knownTagVersionsTtl > 0) {
                foreach ($this->knownTagVersions as $tag => [$exp, $ver]) {
                    if ($now > $exp) {
                        unset($this->knownTagVersions[$tag]);
                    } else {
                        // Since we use FIFO, once we hit a non-expired entry, all following entries are also valid
                        break;
                    }
                }
            }
        }

        return $versions;
    }

    /**
     * Persist the tag envelope for a newly-computed cache entry.
     * The envelope records the current version of every tag so that future
     * reads can detect whether any tag has been invalidated since.
     *
     * Uses getOrCreateTagVersions so that a version is always written (non-empty),
     * which ensures delete-based invalidation works correctly even after multiple
     * back-to-back invalidations of the same tag.
     *
     * @param  string[]  $tags
     */
    private function saveTagEnvelope(string $key, array $tags, int $expiration): void
    {
        $envKey = $this->tagEnvelopeKey($key);

        // Use getOrCreateTagVersions: creates a new version for any tag that
        // has no current version (first use or just after invalidation).
        $envelope = $this->getOrCreateTagVersions($tags);

        $ttl = max(0, $expiration - time());
        $item = $this->tagPool->getItem($envKey);
        $item->set($envelope);
        $item->expiresAfter($ttl > 0 ? $ttl : null);
        $this->tagPool->save($item);
    }

    /**
     * Return the current version token for each tag, creating a new one for
     * any tag that currently has no version (first use or post-invalidation).
     *
     * Called only during save so that every freshly cached item always has a
     * non-empty version in its envelope, which makes delete-based invalidation
     * (invalidateTags) work correctly across repeated invalidations of the same tag.
     *
     * Tag version keys are stored with no expiry (null TTL) — matching Symfony's
     * policy. A version that expires before the items referencing it would cause
     * those items to see '' as the current version, potentially matching items
     * whose envelopes also stored '' and making them look valid forever.
     *
     * @param  string[]  $tags
     *
     * @return array<string, string>  tag => version
     */
    private function getOrCreateTagVersions(array $tags): array
    {
        $versions = [];
        $now = microtime(true);

        foreach ($tags as $tag) {
            // Check in-memory cache first.
            if ($this->knownTagVersionsTtl > 0 && isset($this->knownTagVersions[$tag])) {
                [$exp, $v] = $this->knownTagVersions[$tag];

                if ($now <= $exp && $v !== '') {
                    $versions[$tag] = $v;
                    continue;
                }
            }

            $tagKey = $this->tagVersionKey($tag);
            $tagItem = $this->tagPool->getItem($tagKey);

            if ($tagItem->isHit()) {
                $version = (string) $tagItem->get();
            } else {
                $version = '';
            }

            // Empty means "no effective version"; create a fresh non-empty token.
            if ($version === '') {
                $version = $this->generateTagVersion();
                $tagItem->set($version);
                $tagItem->expiresAfter(null); // tag version keys never expire (Symfony policy)
                $this->tagPool->save($tagItem);
            }

            $versions[$tag] = $version;

            // Update in-memory cache.
            if ($this->knownTagVersionsTtl > 0) {
                unset($this->knownTagVersions[$tag]); // FIFO: re-add at end
                $this->knownTagVersions[$tag] = [$now + $this->knownTagVersionsTtl, $version];
            }
        }

        return $versions;
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

        // Use CacheItemPoolInterface::getItem
        // CachePool will automatically unserialize the data using its serializer
        $item = $this->tagPool->getItem($envKey);

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

    /**
     * Retrieve the tags that were previously stored with a cache item.
     * Returns empty array if the item has no tag envelope.
     *
     * @return string[]
     */
    private function getItemTags(string $key): array
    {
        $envKey = $this->tagEnvelopeKey($key);

        // Use CacheItemPoolInterface::getItem
        // CachePool will automatically unserialize the data using its serializer
        $item = $this->tagPool->getItem($envKey);

        if (!$item->isHit()) {
            return [];
        }

        $storedVersions = $item->get();

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

    public function getTagPool(): CacheItemPoolInterface|false
    {
        return $this->tagPool;
    }

    public function withTagPool(StorageInterface|CacheItemPoolInterface|null|false $tagPool): static
    {
        $new = clone $this;
        $new->applyTagPool($tagPool);

        return $new;
    }

    private function applyTagPool(StorageInterface|CacheItemPoolInterface|null|false $tagPool): void
    {
        if ($tagPool instanceof CacheItemPoolInterface) {
            $pool = $tagPool;
        } elseif ($tagPool !== false) {
            if ($tagPool instanceof StorageInterface) {
                $storage = $tagPool;
            } else {
                $storage = $this->storage;
            }

            $pool = new CachePool(
                $storage,
                new PhpSerializer(),
                $this->logger,
                null,
                false
            );
        } else {
            $pool = false;
        }

        $this->tagPool = $pool;
    }

    /**
     * Get the TTL for in-memory tag version cache (in seconds).
     *
     * @return float
     */
    public function getKnownTagVersionsTtl(): float
    {
        return $this->knownTagVersionsTtl;
    }

    /**
     * Return a new instance with the given TTL for in-memory tag version cache (in seconds).
     *
     * This cache reduces storage I/O when the same tags are checked multiple
     * times within a single request. Default is 0.15 seconds (150ms).
     *
     * Set to 0 to disable the cache entirely (useful for testing or when
     * tag versions are expected to change very frequently).
     *
     * @param  float  $knownTagVersionsTtl  TTL in seconds (0 = disabled)
     *
     * @return static
     */
    public function withKnownTagVersionsTtl(float $knownTagVersionsTtl): static
    {
        $new = clone $this;
        $new->knownTagVersionsTtl = max(0, $knownTagVersionsTtl);

        return $new;
    }

    /**
     * Return a new instance with the in-memory tag versions cache cleared.
     *
     * This is useful to force a fresh read from storage on the next tag check.
     *
     * @return static
     */
    public function withoutKnownTagVersionsCache(): static
    {
        $new = clone $this;
        $new->knownTagVersions = [];

        return $new;
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
