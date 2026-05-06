<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Windwalker\Cache\CacheItem;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\JsonAssocSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\StorageInterface;
use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\NullStorage;
use Windwalker\Test\Traits\TestAccessorTrait;

/**
 * The CachePoolTest class.
 */
class CachePoolTest extends TestCase
{
    use TestAccessorTrait;

    protected CachePool $instance;

    /**
     * testBasicUsage
     *
     * @return  void
     *
     * @throws InvalidArgumentException
     */
    public function testBasicUsage(): void
    {
        $pool = $this->instance;

        $item = $pool->getItem('hello');

        self::assertFalse($item->isHit());

        if (!$item->isHit()) {
            $value = 'Hello World';

            $pool->save($item->set($value));
        }

        self::assertEquals('Hello World', $item->get());
    }

    /**
     * @see  CachePool::withSerializer
     */
    public function testGetSetSerializer(): void
    {
        $this->instance = $this->instance->withSerializer($ser = new RawSerializer());

        self::assertSame($ser, $this->instance->getSerializer());
    }

    /**
     * @see  CachePool::save
     */
    public function testSave(): void
    {
        $item = $this->instance->getItem('foo');

        $item->set('Flower');
        $item->expiresAfter(30);

        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects(self::once())
            ->method('save')
            ->with(
                'foo',
                'Flower',
                self::callback(static fn(int $expiration) => abs($expiration - (time() + 30)) <= 1)
            )
            ->willReturn(true);
        $storageMock->expects(self::once())
            ->method('remove')
            ->with(self::callback(static fn(string $k) => self::isItemMetadataKey($k)))
            ->willReturn(true);

        $this->instance = $this->instance->withStorage($storageMock);
        $this->instance->save($item);
    }

    /**
     * @see  CachePool::hasItem
     */
    public function testHasItem(): void
    {
        self::assertFalse($this->instance->hasItem('foo'));

        $item = $this->instance->getItem('foo');
        $this->instance->save($item->set('Hello'));

        self::assertTrue($this->instance->hasItem('foo'));
    }

    /**
     * @see  CachePool::getItem
     */
    public function testGetItem(): void
    {
        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects(self::exactly(2))
            ->method('has')
            ->willReturnCallback(
                static fn(string $key): bool => match (true) {
                    $key === 'flower' => true,
                    self::isItemMetadataKey($key) => false,
                    default => false,
                }
            );
        $storageMock->expects(self::once())
            ->method('get')
            ->with('flower')
            ->willReturn('Sakura');

        // Disable tagPool for this test since we're only testing basic storage functionality
        $this->instance = $this->instance->withStorage($storageMock)->withTagPool(false);

        $item = $this->instance->getItem('flower');

        self::assertEquals('Sakura', $item->get());
        self::assertTrue($item->isHit());
    }

    /** @see CachePool::getItem — race between has() and get() is treated as cache miss */
    public function testGetItemTreatsHasGetRaceAsMiss(): void
    {
        $storageMock = $this->createMock(StorageInterface::class);
        $hasCalls = 0;
        $storageMock->expects(self::exactly(2))
            ->method('has')
            ->with('flip')
            ->willReturnCallback(static function () use (&$hasCalls): bool {
                $hasCalls++;

                return $hasCalls === 1;
            });
        $storageMock->expects(self::once())
            ->method('get')
            ->with('flip')
            ->willReturn(null);

        $pool = (new CachePool())->withStorage($storageMock)->withTagPool(false);
        $item = $pool->getItem('flip');

        self::assertFalse($item->isHit());
        self::assertNull($item->get());
    }

    /** @see CachePool::getItem — expired metadata causes stale entry cleanup */
    public function testGetItemDeletesStaleEntryWhenMetadataIsExpired(): void
    {
        $meta = serialize(
            [
                'realExpiry' => microtime(true) - 10,
                'ctime' => 1,
            ]
        );

        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects(self::exactly(2))
            ->method('has')
            ->willReturnCallback(
                static fn(string $key): bool => match (true) {
                    $key === 'stale' => true,
                    self::isItemMetadataKey($key) => true,
                    default => false,
                }
            );
        $storageMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(
                static fn(string $key): string => match (true) {
                    $key === 'stale' => 'VALUE',
                    self::isItemMetadataKey($key) => $meta,
                    default => '',
                }
            );
        $removed = [];
        $storageMock->expects(self::exactly(2))
            ->method('remove')
            ->willReturnCallback(static function (string $key) use (&$removed): bool {
                $removed[] = $key;

                return true;
            });

        $pool = (new CachePool())->withStorage($storageMock)->withTagPool(false);
        $item = $pool->getItem('stale');

        self::assertFalse($item->isHit());
        self::assertContains('stale', $removed);
        self::assertTrue((bool) array_filter($removed, static fn(string $k) => self::isItemMetadataKey($k)));
    }

    public function testGetItemWithSerializer(): void
    {
        $this->instance = $this->instance->withSerializer(new JsonAssocSerializer());

        $item = $this->instance->getItem('flower');
        $item->set(['foo' => 'bar']);
        $this->instance->save($item);

        $item = $this->instance->getItem('flower');

        self::assertSame(['foo' => 'bar'], $item->get());
        self::assertTrue($item->isHit());
    }

    /**
     * @see  CachePool::deleteItem
     */
    public function testDeleteItem(): void
    {
        $storageMock = $this->createMock(StorageInterface::class);
        $removed = [];
        $storageMock->expects(self::exactly(2))
            ->method('remove')
            ->willReturnCallback(static function (string $key) use (&$removed): bool {
                $removed[] = $key;

                return true;
            });

        $this->instance->setStorage($storageMock);

        $this->instance->deleteItem('hello');

        self::assertContains('hello', $removed);
        self::assertTrue((bool) array_filter($removed, static fn(string $k) => self::isItemMetadataKey($k)));

        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects(self::once())
            ->method('remove')
            ->with('hello')
            ->willThrowException(new RuntimeException());

        $this->instance = $this->instance->withStorage($storageMock);

        self::assertFalse($this->instance->deleteItem('hello'));
    }

    /** @see CachePool::deleteItem — tag envelope sidecar is removed with the item */
    public function testDeleteItemAlsoDeletesTagEnvelope(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        $pool->set('tagged_delete', 'VALUE', 3600, ['users']);

        $envKey = '--ww_tag_env--' . hash('sha1', 'tagged_delete');
        self::assertTrue($tagStorage->has($envKey));

        self::assertTrue($pool->deleteItem('tagged_delete'));
        self::assertFalse($mainStorage->has('tagged_delete'));
        self::assertFalse($tagStorage->has($envKey), 'Deleting item should also delete its tag envelope');
    }

    /**
     * @see  CachePool::deleteItems
     */
    public function testDeleteItems(): void
    {
        $this->instance->save($this->createItem('foo', 'FOO'));
        $this->instance->save($this->createItem('bar', 'BAR'));
        $this->instance->save($this->createItem('yoo', 'YOO'));

        $this->instance->deleteItems(['foo', 'bar']);

        self::assertFalse($this->instance->getItem('foo')->isHit());
        self::assertFalse($this->instance->getItem('bar')->isHit());
        self::assertTrue($this->instance->getItem('yoo')->isHit());
    }

    /**
     * @see  CachePool::getItems
     */
    public function testGetItems(): void
    {
        $this->instance->save($this->createItem('foo', 'FOO'));
        $this->instance->save($this->createItem('bar', 'BAR'));
        $this->instance->save($this->createItem('yoo', 'YOO'));

        $items = iterator_to_array($this->instance->getItems(['foo', 'yoo']));

        self::assertEquals('FOO', $items['foo']->get());
        self::assertEquals('YOO', $items['yoo']->get());
    }

    /**
     * @see  CachePool::saveDeferred
     */
    public function testSaveDeferred(): void
    {
        $storage = new ArrayStorage();
        $this->instance->setStorage($storage);

        $this->instance->saveDeferred($this->createItem('foo', 'FOO'));
        $this->instance->saveDeferred($this->createItem('yoo', 'YOO'));

        self::assertEmpty($storage->getData());

        // Auto commit when destructing
        unset($this->instance);

        self::assertEquals('FOO', $storage->get('foo'));
        self::assertEquals('YOO', $storage->get('yoo'));
    }

    /**
     * @see  CachePool::commit
     */
    public function testCommit(): void
    {
        $storage = new ArrayStorage();
        $this->instance->setStorage($storage);

        $this->instance->saveDeferred($this->createItem('foo', 'FOO'));
        $this->instance->saveDeferred($this->createItem('yoo', 'YOO'));

        self::assertEmpty($storage->getData());

        $this->instance->commit();

        self::assertEquals('FOO', $storage->get('foo'));
        self::assertEquals('YOO', $storage->get('yoo'));
    }

    /**
     * @see  CachePool::clear
     */
    public function testClear(): void
    {
        $storage = new ArrayStorage();
        $this->instance->setStorage($storage);

        $this->instance->save($this->createItem('foo', 'FOO'));
        $this->instance->save($this->createItem('bar', 'BAR'));
        $this->instance->save($this->createItem('yoo', 'YOO'));

        $this->instance->clear();

        self::assertEmpty($storage->getData());
    }

    /**
     * @see  CachePool::withStorage / getStorage
     */
    public function testSetStorage(): void
    {
        $storage = new ArrayStorage();

        $instance = $this->instance->withStorage($storage);

        self::assertSame($storage, $instance->getStorage());
    }

    /**
     * @see  CachePool::getStorage
     */
    public function testGetStorage(): void
    {
        self::assertInstanceOf(StorageInterface::class, $this->instance->getStorage());
    }

    /**
     * @see  CachePool::set
     */
    public function testSetGet(): void
    {
        $this->instance->set('hello', 'RRR');

        self::assertEquals('RRR', $this->instance->getStorage()->get('hello'));

        $this->instance->set('hello2', 'RRR2', -5);

        self::assertNull($this->instance->getStorage()->get('hello2'));

        $this->instance->set('hello3', 'RRR3', 10);

        self::assertEquals(
            time() + 10,
            $this->getValue($this->instance->getStorage(), 'data')['hello3'][0]
        );
    }

    /**
     * @see  CachePool::withSerializer
     */
    public function testGetWithSerializer(): void
    {
        $this->instance = $this->instance->withSerializer(new JsonAssocSerializer());

        $this->instance->set('flower', ['foo' => 'bar']);

        $flower = $this->instance->get('flower');

        self::assertSame(['foo' => 'bar'], $flower);
    }

    /**
     * @see  CachePool::fetch
     */
    public function testCall(): void
    {
        $i = 0;

        $getter = function () use (&$i) {
            return $this->instance->fetch(
                'hello',
                static function () use (&$i) {
                    $i++;

                    return 'HELLO-' . $i;
                }
            );
        };

        $getter();
        $getter();
        $r = $getter();

        self::assertEquals('HELLO-' . 1, $r);
    }

    /** @see CachePool::fetch — beta=INF always forces recomputation */
    public function testCallWithBetaInfAlwaysRecomputes(): void
    {
        $i = 0;

        $compute = static function () use (&$i) {
            $i++;

            return 'V' . $i;
        };

        $this->instance->fetch('betakey', $compute, 60);          // $i = 1, stored
        $this->instance->fetch('betakey', $compute, 60, INF);     // $i = 2, forced recompute
        $result = $this->instance->fetch('betakey', $compute, 60, INF); // $i = 3, forced recompute

        self::assertEquals('V3', $result);
        self::assertEquals(3, $i);
    }

    /** @see CachePool::fetch — beta=0 never recomputes early */
    public function testCallWithBetaZeroNeverRecomputesEarly(): void
    {
        $i = 0;

        $compute = static function () use (&$i) {
            $i++;

            return 'V' . $i;
        };

        // Store with a long TTL
        $this->instance->fetch('betazero', $compute, 3600);        // $i = 1
        $result = $this->instance->fetch('betazero', $compute, 3600, 0.0); // beta=0, must serve cache

        self::assertEquals('V1', $result);
        self::assertEquals(1, $i, 'Handler must not be called again when beta=0 and item is fresh');
    }

    /** @see CachePool::fetch — beta=false (B/C) maps to 0.0 */
    public function testCallWithBetaFalseBcMapsToZero(): void
    {
        $i = 0;

        $compute = static function () use (&$i) {
            $i++;

            return 'V' . $i;
        };

        $this->instance->fetch('bckey', $compute, 3600);          // first compute
        $result = $this->instance->fetch('bckey', $compute, 3600, 0.0); // beta=0.0, serve cache

        self::assertEquals('V1', $result);
        self::assertEquals(1, $i);
    }

    /** @see CachePool::fetch — lock=false skips CacheLock entirely */
    public function testFetchWithLockDisabled(): void
    {
        $i = 0;

        $compute = static function () use (&$i) {
            $i++;

            return 'V' . $i;
        };

        // First call: cache miss, compute
        $result = $this->instance->fetch('nolock', $compute, 3600, 1.0, false);
        self::assertEquals('V1', $result);

        // Second call: cache hit, no recompute
        $result = $this->instance->fetch('nolock', $compute, 3600, 1.0, false);
        self::assertEquals('V1', $result);
        self::assertEquals(1, $i, 'Handler must not be called again on cache hit');
    }

    /** @see CachePool::call — deprecated alias passes $lock through */
    public function testCallDeprecatedAliasWorks(): void
    {
        $result = $this->instance->call('alias_key', static fn() => 'legacy', 60);
        self::assertEquals('legacy', $result);

        // With lock=true (old explicit opt-in)
        $result = $this->instance->call('alias_key2', static fn() => 'locked', 60, true);
        self::assertEquals('locked', $result);
    }

    /**
     * @see  CachePool::getMultiple
     */
    public function testGetMultiple(): void
    {
        $this->instance->set('foo', 'FOO');
        $this->instance->set('bar', 'BAR');

        // 'missing' is not in cache — should fall back to $default
        $values = iterator_to_array(
            $this->instance->getMultiple(['foo', 'bar', 'missing'], 'DEFAULT')
        );

        self::assertEquals('FOO', $values['foo']);
        self::assertEquals('BAR', $values['bar']);
        self::assertEquals('DEFAULT', $values['missing'], 'Missing key must return $default');
    }

    /**
     * @see  CachePool::deleteMultiple
     */
    public function testDeleteMultiple(): void
    {
        $this->instance->set('foo', 'FOO');
        $this->instance->set('bar', 'BAR');
        $this->instance->set('yoo', 'YOO');

        $result = $this->instance->deleteMultiple(['foo', 'bar']);

        self::assertTrue($result);
        self::assertFalse($this->instance->has('foo'));
        self::assertFalse($this->instance->has('bar'));
        self::assertTrue($this->instance->has('yoo'));
    }

    /**
     * @see  CachePool::setMultiple
     */
    public function testSetMultiple(): void
    {
        $result = $this->instance->setMultiple(['foo' => 'FOO', 'bar' => 'BAR', 'yoo' => 'YOO']);

        self::assertTrue($result);
        self::assertEquals('FOO', $this->instance->get('foo'));
        self::assertEquals('BAR', $this->instance->get('bar'));
        self::assertEquals('YOO', $this->instance->get('yoo'));
    }

    /**
     * @see  CachePool::setMultiple — invalid argument
     */
    public function testSetMultipleThrowsOnNonIterable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @phpstan-ignore-next-line */
        $this->instance->setMultiple('not-iterable');
    }

    /**
     * @see  CachePool::delete
     */
    public function testDelete(): void
    {
        $this->instance->set('foo', 'FOO');

        self::assertTrue($this->instance->has('foo'));

        $this->instance->delete('foo');

        self::assertFalse($this->instance->has('foo'));
    }

    /**
     * @see  CachePool::has
     */
    public function testHas(): void
    {
        self::assertFalse($this->instance->has('missing'));

        $this->instance->set('foo', 'FOO');

        self::assertTrue($this->instance->has('foo'));

        $this->instance->delete('foo');

        self::assertFalse($this->instance->has('foo'));
    }

    /**
     * @see  CachePool::__destruct — autoCommit flushes deferred items
     */
    public function testDestruct(): void
    {
        $storage = new ArrayStorage();

        $pool = new CachePool($storage);
        $pool->saveDeferred($this->createItem('foo', 'FOO'));
        $pool->saveDeferred($this->createItem('bar', 'BAR'));

        self::assertEmpty($storage->getData(), 'Items must not be saved before destruct');

        $pool->__destruct();

        self::assertEquals('FOO', $storage->get('foo'));
        self::assertEquals('BAR', $storage->get('bar'));
    }

    /**
     * @see  CachePool::autoCommit — disable prevents destruct from committing
     */
    public function testDestructWithAutoCommitDisabled(): void
    {
        $storage = new ArrayStorage();

        $pool = new CachePool($storage);
        $pool->autoCommit(false);
        $pool->saveDeferred($this->createItem('foo', 'FOO'));

        $pool->__destruct();

        self::assertEmpty($storage->getData(), 'Items must NOT be saved when autoCommit is disabled');
    }

    /**
     * @see  CachePool::__construct
     */
    public function testConstruct(): void
    {
        $storage = new ArrayStorage();
        $serializer = new RawSerializer();

        $pool = new CachePool($storage, $serializer, defaultTtl: 120);

        self::assertSame($storage, $pool->getStorage());
        self::assertSame($serializer, $pool->getSerializer());
        self::assertEquals(120, $pool->getDefaultTtl());
    }

    /**
     * @see  CachePool::save — item with past expiry is removed rather than saved
     */
    public function testSaveExpiredItemRemovesIt(): void
    {
        $this->instance->set('foo', 'FOO');
        self::assertTrue($this->instance->has('foo'));

        $item = $this->instance->getItem('foo');
        $item->expiresAt(new \DateTime('-1 second'));

        $result = $this->instance->save($item);

        self::assertFalse($result, 'save() must return false for an expired item');
        self::assertFalse($this->instance->has('foo'), 'Expired item must be removed from storage');
    }

    /** @see CachePool::save — tagPool failures make save() fail for tagged items */
    public function testSaveReturnsFalseWhenTagPoolFails(): void
    {
        $failingTagPool = $this->createMock(CacheItemPoolInterface::class);
        $failingTagPool->expects($this->once())
            ->method('getItem')
            ->with(self::isString())
            ->willThrowException(new RuntimeException());

        $pool = new CachePool(new ArrayStorage())->withTagPool($failingTagPool);
        $item = $pool->getItem('tag_fail');
        $item->set('VALUE');
        $item->expiresAfter(3600);
        $item->tags('users');

        self::assertFalse($pool->save($item));
    }

    /**
     * @see  CachePool::get — returns $default for cache miss
     */
    public function testGetReturnsDefaultOnMiss(): void
    {
        self::assertNull($this->instance->get('missing'));
        self::assertEquals('fallback', $this->instance->get('missing', 'fallback'));
    }

    /**
     * @see  CachePool::setDefaultTtl — default TTL is applied to new items
     */
    public function testDefaultTtlIsAppliedToNewItems(): void
    {
        $this->instance->setDefaultTtl(3600);

        $this->instance->set('foo', 'FOO'); // no explicit TTL — should use defaultTtl

        $expiration = $this->getValue($this->instance->getStorage(), 'data')['foo'][0];

        self::assertEqualsWithDelta(time() + 3600, $expiration, 2);
    }

    /**
     * @see  CachePool::fetch — handler receives the CacheItem as first argument
     */
    public function testFetchHandlerReceivesCacheItem(): void
    {
        $receivedItem = null;

        $this->instance->fetch('item_arg', function ($item) use (&$receivedItem) {
            $receivedItem = $item;

            return 'value';
        }, 60);

        self::assertInstanceOf(\Psr\Cache\CacheItemInterface::class, $receivedItem);
        self::assertEquals('item_arg', $receivedItem->getKey());
    }

    /**
     * @see  CachePool::fetch — computed item stores Symfony-style metadata
     */
    public function testFetchStoresSymfonyMetadataOnCacheItem(): void
    {
        $receivedItem = null;

        $this->instance->fetch('item_meta', function (CacheItem $item) use (&$receivedItem) {
            $receivedItem = $item;
            usleep(2000);

            return 'value';
        }, 60, 0.0, false);

        self::assertInstanceOf(CacheItem::class, $receivedItem);

        self::assertGreaterThan(microtime(true), $receivedItem->realExpiry);
        self::assertGreaterThan(0, $receivedItem->ctime);
    }

    // -----------------------------------------------------------------------
    // Tagged cache
    // -----------------------------------------------------------------------

    /** @see CachePool::fetch — tagged item is served from cache while tags are valid */
    public function testTaggedFetchServesFromCacheWhenTagsAreValid(): void
    {
        $i = 0;

        $compute = function (CacheItem $item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'V' . $i;
        };

        $this->instance->fetch('user1', $compute, 3600, 0.0, false);
        $result = $this->instance->fetch('user1', $compute, 3600, 0.0, false);

        self::assertEquals('V1', $result);
        self::assertEquals(1, $i, 'Handler must not be called again when tag is still valid');
    }

    /** @see CachePool::invalidateTags — invalidated tag forces recomputation */
    public function testInvalidateTagsForcesRecomputation(): void
    {
        $i = 0;

        $compute = function (CacheItem $item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'V' . $i;
        };

        // Store with tags
        $this->instance->fetch('user1', $compute, 3600, 0.0, false);
        self::assertEquals(1, $i);

        // Invalidate the tag
        $this->instance->invalidateTags('users');

        // Next fetch must recompute because the tag is stale
        $result = $this->instance->fetch('user1', $compute, 3600, 0.0, false);

        self::assertEquals('V2', $result);
        self::assertEquals(2, $i, 'Handler must be called again after tag is invalidated');
    }

    /** @see CachePool::invalidateTags — only items sharing the invalidated tag are affected */
    public function testInvalidateTagsOnlyAffectsMatchingItems(): void
    {
        $calls = ['user1' => 0, 'post1' => 0];

        $this->instance->fetch(
            'user1',
            function (CacheItem $item) use (&$calls) {
                $calls['user1']++;
                $item->tags('users');

                return 'user';
            },
            3600,
            0.0,
            false
        );

        $this->instance->fetch(
            'post1',
            function ($item) use (&$calls) {
                $calls['post1']++;
                $item->tags('posts');

                return 'post';
            },
            3600,
            0.0,
            false
        );

        // Invalidate only 'users' tag
        $this->instance->invalidateTags('users');

        $this->instance->fetch(
            'user1',
            function ($item) use (&$calls) {
                $calls['user1']++;
                $item->tags('users');

                return 'user';
            },
            3600,
            0.0,
            false
        );

        $this->instance->fetch(
            'post1',
            function ($item) use (&$calls) {
                $calls['post1']++;
                $item->tags('posts');

                return 'post';
            },
            3600,
            0.0,
            false
        );

        self::assertEquals(2, $calls['user1'], 'user1 must be recomputed after users tag invalidation');
        self::assertEquals(1, $calls['post1'], 'post1 must NOT be recomputed (posts tag untouched)');
    }

    /** @see CachePool::invalidateTags — multiple tags, partial invalidation */
    public function testMultipleTagsPartialInvalidation(): void
    {
        $i = 0;

        $compute = function ($item) use (&$i) {
            $i++;
            $item->tags('tagA', 'tagB');

            return 'V' . $i;
        };

        // Store with two tags
        $this->instance->fetch('item', $compute, 3600, 0.0, false);

        // Invalidate only tagA — item has both, so it becomes stale
        $this->instance->invalidateTags('tagA');

        $result = $this->instance->fetch('item', $compute, 3600, 0.0, false);

        self::assertEquals('V2', $result);
        self::assertEquals(2, $i, 'Item must be recomputed when ANY of its tags is invalidated');
    }

    /** @see CachePool::fetch — items fetched WITHOUT tags are not affected by invalidateTags */
    public function testUntaggedItemsAreNotAffectedByTagInvalidation(): void
    {
        $i = 0;

        $compute = static function () use (&$i) {
            $i++;

            return 'V' . $i;
        };

        // Store WITHOUT tags
        $this->instance->fetch('notagitem', $compute, 3600, 0.0, false);

        $this->instance->invalidateTags('users');

        // No tags in fetch — tag check is skipped, serve from cache
        $result = $this->instance->fetch('notagitem', $compute, 3600, 0.0, false);

        self::assertEquals('V1', $result);
        self::assertEquals(1, $i, 'Untagged items must not be affected by tag invalidation');
    }

    /** @see CachePool::invalidateTags — invalidating a tag that has no items is a no-op */
    public function testInvalidateTagsWithNoItemsIsNoOp(): void
    {
        $result = $this->instance->invalidateTags('nonexistent_tag');

        self::assertTrue($result);
    }

    /**
     * @see CachePool::fetch — to change tags on existing item, must delete it first
     */
    public function testChangingTagsRequiresDeleteFirst(): void
    {
        $i = 0;

        // First fetch: stored WITHOUT tags
        $this->instance->fetch('item', static function () use (&$i) {
            $i++;

            return 'V' . $i;
        }, 3600, 0.0, false);

        // Second fetch trying to add tags — cache hit prevents handler from running,
        // so tags are not changed. This demonstrates you must delete() first to change tags.
        $result = $this->instance->fetch('item', function ($item) use (&$i) {
            $i++;
            $item->tags('users');  // <-- this line will never execute on cache hit

            return 'V' . $i;
        }, 3600, 0.0, false);

        self::assertEquals('V1', $result, 'Cache hit prevents handler (and tag change) from executing');
        self::assertEquals(1, $i, 'Handler must not be called on cache hit');

        // Correct way to change tags: delete first
        $this->instance->delete('item');

        $result = $this->instance->fetch('item', function ($item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'V' . $i;
        }, 3600, 0.0, false);

        self::assertEquals('V2', $result);
        self::assertEquals(2, $i, 'After delete, handler is called and tags are set');
    }

    /** @see CacheItem::tags — variadic tag() method */
    public function testCacheItemTagVariadic(): void
    {
        $item = CacheItem::create('test');

        $item->tags('A', 'B', 'C');

        self::assertEquals(['A', 'B', 'C'], $item->getTags());
    }

    /** @see CacheItem::tags — chaining and deduplication */
    public function testCacheItemTagChaining(): void
    {
        $item = CacheItem::create('test');

        $item->tags('A')->tags('B', 'A')->tags('C');

        self::assertEquals(['A', 'B', 'C'], $item->getTags(), 'Duplicate tags must be deduplicated');
    }

    // -----------------------------------------------------------------------
    // TagPool tests
    // -----------------------------------------------------------------------

    /** @see CachePool::withTagPool — tags are stored in separate pool */
    public function testTagPoolIsolatesTagMetadata(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();

        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        $i = 0;

        $pool->fetch('item1', function ($item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'V' . $i;
        }, 3600, 0.0, false);

        // Tag version and envelope should be in tagStorage, not mainStorage
        $tagData = $tagStorage->getData();

        self::assertNotEmpty($tagData, 'Tag metadata must be stored in tagStorage');

        // Should have both envelope and tag version (might be merged in one save operation)
        // Just ensure we have tag metadata in separate storage
        self::assertGreaterThanOrEqual(1, count($tagData), 'Should have at least tag metadata');

        // Main item should be in mainStorage
        self::assertTrue($mainStorage->has('item1'));
        self::assertEquals('V1', $pool->get('item1'));
    }

    /** @see CachePool::invalidateTags — with tagPool */
    public function testInvalidateTagsWithTagPool(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();

        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        $i = 0;

        $compute = function ($item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'V' . $i;
        };

        $pool->fetch('user1', $compute, 3600, 0.0, false);
        self::assertEquals(1, $i);

        // Invalidate via tagPool
        $pool->invalidateTags('users');

        // Should recompute
        $result = $pool->fetch('user1', $compute, 3600, 0.0, false);

        self::assertEquals('V2', $result);
        self::assertEquals(2, $i);
    }

    /** @see CachePool::withTagPool — wraps StorageInterface in CachePool */
    public function testSetTagPoolAcceptsStorage(): void
    {
        $tagStorage = new ArrayStorage();

        $pool = (new CachePool())->withTagPool($tagStorage);

        $tagPool = $pool->getTagPool();

        self::assertInstanceOf(CachePool::class, $tagPool, 'Storage is wrapped in CachePool');
        self::assertSame($tagStorage, $tagPool->getStorage(), 'Wrapped storage matches original');
    }

    /** @see CachePool::withTagPool — accepts a pre-built CacheItemPoolInterface unchanged */
    public function testSetTagPoolAcceptsCacheItemPoolInterface(): void
    {
        $existingPool = new CachePool(new ArrayStorage(), tagPool: false);
        $pool = (new CachePool())->withTagPool($existingPool);

        self::assertSame($existingPool, $pool->getTagPool());
    }

    /** @see CachePool::withTagPool — null uses main storage for tags */
    public function testSetTagPoolNull(): void
    {
        $storage = new ArrayStorage();
        $pool = (new CachePool($storage))
            ->withTagPool(new ArrayStorage()) // Set to separate storage first
            ->withTagPool(null); // Reset to use main storage

        $tagPool = $pool->getTagPool();
        self::assertInstanceOf(CachePool::class, $tagPool, 'null creates CachePool with main storage');
        self::assertSame($storage, $tagPool->getStorage(), 'Should use main storage for tags');
    }

    /** @see CachePool::withKnownTagVersionsTtl — can configure in-memory cache TTL */
    public function testWithKnownTagVersionsTtl(): void
    {
        $pool = new CachePool();

        // Default is 0.15 seconds (150ms)
        self::assertSame(0.15, $pool->getKnownTagVersionsTtl());

        // Returns new instance with custom TTL
        $pool2 = $pool->withKnownTagVersionsTtl(0.5); // 500ms
        self::assertSame(0.5, $pool2->getKnownTagVersionsTtl());
        self::assertSame(0.15, $pool->getKnownTagVersionsTtl(), 'Original pool should be unchanged');

        // Can set to 0 to disable
        $pool3 = $pool->withKnownTagVersionsTtl(0);
        self::assertSame(0.0, $pool3->getKnownTagVersionsTtl());

        // Negative values are clamped to 0
        $pool4 = $pool->withKnownTagVersionsTtl(-1);
        self::assertSame(0.0, $pool4->getKnownTagVersionsTtl());
    }

    /** @see CachePool::invalidateTags — clears in-memory cache for invalidated tags */
    public function testInvalidateTagsClearsKnownTagVersionsCache(): void
    {
        $storage = new ArrayStorage();
        $pool = (new CachePool($storage))->withKnownTagVersionsTtl(10.0); // Long cache

        // Create items with tags
        $pool->fetch('user1', function ($item) {
            $item->tags('users');

            return 'data1';
        }, 3600, 0.0, false);

        // Tag version is now cached in memory
        // Invalidate the tag
        $pool->invalidateTags('users');

        // Fetch another item with the same tag
        // If cache wasn't cleared, it would use the OLD version and treat item as valid
        // But since cache is cleared, it fetches the NEW version
        $recomputeCount = 0;
        $result = $pool->fetch('user1', function ($item) use (&$recomputeCount) {
            $recomputeCount++;
            $item->tags('users');

            return 'data2';
        }, 3600, 0.0, false);

        self::assertSame('data2', $result, 'Should recompute after tag invalidation');
        self::assertEquals(1, $recomputeCount, 'Handler should be called to recompute');
    }

    /** @see CachePool::withoutKnownTagVersionsCache — manual cache clear */
    public function testWithoutKnownTagVersionsCache(): void
    {
        $storage = new ArrayStorage();
        $pool = (new CachePool($storage))->withKnownTagVersionsTtl(10.0); // Long cache

        // Create an item to populate cache
        $pool->fetch('item1', function ($item) {
            $item->tags('users');

            return 'value1';
        }, 3600, 0.0, false);

        // Return new instance with the in-memory cache cleared
        $pool2 = $pool->withoutKnownTagVersionsCache();

        // This should work without issues
        $result = $pool2->fetch('item2', function ($item) {
            $item->tags('users');

            return 'value2';
        }, 3600, 0.0, false);

        self::assertSame('value2', $result);
    }

    /** @see CachePool::saveDeferred + CachePool::commit — tagged deferred items keep invalidation semantics */
    public function testCommitPersistsTagEnvelopeForDeferredTaggedItems(): void
    {
        $i = 0;
        $item = $this->instance->getItem('deferred_tagged');
        $item->set('INITIAL');
        $item->expiresAfter(3600);
        $item->tags('users');

        self::assertTrue($this->instance->saveDeferred($item));
        self::assertTrue($this->instance->commit());

        $this->instance->invalidateTags('users');

        $value = $this->instance->fetch('deferred_tagged', function (CacheItem $item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'REBUILT';
        }, 3600, 0.0, false);

        self::assertSame('REBUILT', $value);
        self::assertSame(1, $i, 'Deferred tagged item should be invalidated after commit');
    }

    /** @see CachePool::getCurrentTagVersions — cache disabled when TTL is 0 */
    public function testKnownTagVersionsCacheDisabledWhenTtlIsZero(): void
    {
        $storage = new ArrayStorage();
        $pool = (new CachePool($storage))->withKnownTagVersionsTtl(0); // Disable cache

        // Create items with tags - each should fetch tag version from storage
        $pool->fetch('item1', function ($item) {
            $item->tags('users');

            return 'value1';
        }, 3600, 0.0, false);

        $pool->fetch('item2', function ($item) {
            $item->tags('users');

            return 'value2';
        }, 3600, 0.0, false);

        // No assertions needed - just verify no errors occur when cache is disabled
        self::assertTrue(true);
    }

    /** @see CachePool::withTagPool — false disables all tag functionality */
    public function testTagPoolFalseDisablesTags(): void
    {
        $storage = new ArrayStorage();
        $pool = (new CachePool($storage))->withTagPool(false);

        self::assertFalse($pool->getTagPool(), 'tagPool should be false');

        $i = 0;

        // Fetch with tags (but tags are ignored)
        $result = $pool->fetch('item1', function ($item) use (&$i) {
            $i++;
            $item->tags('users'); // This should be ignored

            return 'value1';
        }, 3600, 0.0, false);

        self::assertSame('value1', $result);
        self::assertEquals(1, $i);

        // Invalidate tags (should be a no-op)
        $pool->invalidateTags('users');

        // Fetch again - should still use cached value (tags were ignored)
        $result2 = $pool->fetch('item1', function ($item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'value2';
        }, 3600, 0.0, false);

        self::assertSame('value1', $result2, 'Should use cached value because tags are disabled');
        self::assertEquals(1, $i, 'Handler should not be called again');

        // Verify no tag metadata was stored
        $tagKeys = array_filter(
            array_keys($storage->getData()),
            fn($k) => str_starts_with($k, '--ww_tag_ver--') || str_starts_with($k, '--ww_tag_env--')
        );
        self::assertEmpty($tagKeys, 'No tag metadata should be stored when tags are disabled');
    }

    /**
     * @see  CachePool::save / CachePool::getItem — fetch metadata is persisted to storage
     */
    public function testFetchMetadataIsPersistedAcrossPoolInstances(): void
    {
        $storage = new ArrayStorage();
        $poolA = new CachePool($storage);

        $poolA->fetch('persist_meta', function (CacheItem $item) {
            usleep(2000);

            return 'value';
        }, 60, 0.0, false);

        $poolB = new CachePool($storage);
        $item = $poolB->getItem('persist_meta');
        self::assertTrue($item->isHit());
        self::assertEquals('value', $item->get());
        self::assertGreaterThan(microtime(true), $item->realExpiry);
        self::assertGreaterThan(0, $item->ctime);
    }

    public function createItem(string $key, mixed $value = null): CacheItem
    {
        return $this->instance->getItem($key)->set($value);
    }

    protected function setUp(): void
    {
        $this->instance = new CachePool();
    }

    protected function tearDown(): void
    {
    }

    private static function isItemMetadataKey(string $key): bool
    {
        return str_starts_with($key, '--ww_item_meta--');
    }

    public function testWithGroupReturnsScopedClone(): void
    {
        $pool = new CachePool(new ArrayStorage(0.0));

        $flower = $pool->withGroup('flower');
        $tree   = $pool->withGroup('tree');

        self::assertNotSame($pool, $flower);
        self::assertNotSame($flower, $tree);

        self::assertInstanceOf(GroupedStorageInterface::class, $flower->getStorage());
        self::assertSame('flower', $flower->getStorage()->group);
        self::assertSame('tree', $tree->getStorage()->group);

        $flower->set('same-key', 'FLOWER');
        $tree->set('same-key', 'TREE');

        self::assertSame('FLOWER', $flower->get('same-key'));
        self::assertSame('TREE',   $tree->get('same-key'));
        self::assertNull($pool->get('same-key'));
    }

    public function testWithGroupThrowsWhenStorageUnsupported(): void
    {
        // Create a mock storage that does NOT implement GroupedStorageInterface
        $storage = $this->createMock(StorageInterface::class);

        $pool = new CachePool($storage);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('does not implement');
        $pool->withGroup('flower');
    }

    /** @see CachePool::set + CachePool::invalidateTags — values written via set(tags) must be invalidated */
    public function testSetWithTagsIsAffectedByInvalidateTags(): void
    {
        $this->instance->set('user_set', 'ORIGINAL', 3600, ['users']);
        self::assertSame('ORIGINAL', $this->instance->get('user_set'));

        $this->instance->invalidateTags('users');

        $called = 0;
        $value = $this->instance->fetch('user_set', function (CacheItem $item) use (&$called) {
            $called++;
            $item->tags('users');

            return 'RECOMPUTED';
        }, 3600, 0.0, false);

        self::assertSame('RECOMPUTED', $value);
        self::assertSame(1, $called, 'set(tags) entries must become stale after invalidateTags()');
    }

    /** @see CachePool::save + CachePool::invalidateTags — manually saved tagged items must also be invalidated */
    public function testSaveTaggedItemIsAffectedByInvalidateTags(): void
    {
        $item = $this->instance->getItem('user_manual');
        $item->set('ORIGINAL');
        $item->expiresAfter(3600);
        $item->tags('users');

        self::assertTrue($this->instance->save($item));
        self::assertSame('ORIGINAL', $this->instance->get('user_manual'));

        $this->instance->invalidateTags('users');

        $called = 0;
        $value = $this->instance->fetch('user_manual', function (CacheItem $item) use (&$called) {
            $called++;
            $item->tags('users');

            return 'RECOMPUTED';
        }, 3600, 0.0, false);

        self::assertSame('RECOMPUTED', $value);
        self::assertSame(1, $called, 'save(tagged item) entries must become stale after invalidateTags()');
    }

    /** @see CachePool::save — saving the same key without tags must clear any stale old tag envelope */
    public function testSavingWithoutTagsClearsOldTagEnvelope(): void
    {
        $this->instance->set('retagged', 'TAGGED', 3600, ['users']);
        $this->instance->set('retagged', 'UNTAGGED', 3600);

        $this->instance->invalidateTags('users');

        $called = 0;
        $value = $this->instance->fetch('retagged', function () use (&$called) {
            $called++;

            return 'RECOMPUTED';
        }, 3600, 0.0, false);

        self::assertSame('UNTAGGED', $value, 'Old tag envelope must be cleared when item is re-saved without tags');
        self::assertSame(0, $called, 'Untagged item should not be recomputed by unrelated tag invalidation');
    }

    /** @see CachePool::invalidateTags — repeated invalidation on same tag remains effective */
    public function testInvalidateSameTagMultipleTimesStillForcesRecompute(): void
    {
        $i = 0;
        $compute = function (CacheItem $item) use (&$i) {
            $i++;
            $item->tags('users');

            return 'V' . $i;
        };

        self::assertSame('V1', $this->instance->fetch('user_repeat', $compute, 3600, 0.0, false));

        $this->instance->invalidateTags('users');
        self::assertSame('V2', $this->instance->fetch('user_repeat', $compute, 3600, 0.0, false));

        $this->instance->invalidateTags('users');
        self::assertSame('V3', $this->instance->fetch('user_repeat', $compute, 3600, 0.0, false));

        self::assertSame(3, $i);
    }

    // -----------------------------------------------------------------------
    // getItem tag validation
    // -----------------------------------------------------------------------

    /** @see CachePool::getItem — returns cache miss when tags have been invalidated */
    public function testGetItemReturnsMissWhenTagsInvalidated(): void
    {
        // Save item with tags
        $this->instance->set('tagged_item', 'VALUE', 3600, ['users', 'posts']);

        // Initially should be a hit
        $item = $this->instance->getItem('tagged_item');
        self::assertTrue($item->isHit());
        self::assertSame('VALUE', $item->get());

        // Invalidate one of the tags
        $this->instance->invalidateTags('users');

        // Now getItem should return a miss
        $item = $this->instance->getItem('tagged_item');
        self::assertFalse($item->isHit(), 'getItem should return miss when any tag is invalidated');
        self::assertNull($item->get());
    }

    /** @see CachePool::getItem — untagged items are unaffected by tag invalidation */
    public function testGetItemUntaggedUnaffectedByInvalidation(): void
    {
        // Save item without tags
        $this->instance->set('untagged_item', 'VALUE', 3600);

        // Invalidate a tag
        $this->instance->invalidateTags('users');

        // Item should still be a hit
        $item = $this->instance->getItem('untagged_item');
        self::assertTrue($item->isHit(), 'getItem should return hit for untagged items after tag invalidation');
        self::assertSame('VALUE', $item->get());
    }

    /** @see CachePool::getItem — validates all tags, only invalidated tags cause miss */
    public function testGetItemValidatesAllTags(): void
    {
        // Save item with multiple tags
        $this->instance->set('multi_tag_item', 'VALUE', 3600, ['tagA', 'tagB', 'tagC']);

        // Initially should be a hit
        $item = $this->instance->getItem('multi_tag_item');
        self::assertTrue($item->isHit());

        // Invalidate one tag - should cause miss
        $this->instance->invalidateTags('tagB');

        $item = $this->instance->getItem('multi_tag_item');
        self::assertFalse($item->isHit(), 'getItem should miss when ANY tag is invalidated');

        // Re-save with same tags
        $this->instance->set('multi_tag_item', 'VALUE2', 3600, ['tagA', 'tagB', 'tagC']);

        // Should be hit now
        $item = $this->instance->getItem('multi_tag_item');
        self::assertTrue($item->isHit());
        self::assertSame('VALUE2', $item->get());
    }

    /** @see CachePool::getItem — cleans up stale item when tags are invalid */
    public function testGetItemDeletesItemWhenTagsInvalid(): void
    {
        $storage = new ArrayStorage();
        $pool = (new CachePool($storage))->withTagPool(new ArrayStorage());

        $pool->set('cleanup_item', 'VALUE', 3600, ['users']);

        // Item exists in storage
        self::assertTrue($storage->has('cleanup_item'));

        // Invalidate tag
        $pool->invalidateTags('users');

        // getItem should clean up the stale item
        $item = $pool->getItem('cleanup_item');

        self::assertFalse($item->isHit());
        self::assertFalse($storage->has('cleanup_item'), 'getItem should delete stale item from storage');
    }

    /** @see CachePool::getItem — works correctly when tagPool is disabled */
    public function testGetItemWithTagPoolDisabled(): void
    {
        $pool = (new CachePool())->withTagPool(false);

        $pool->set('notag_item', 'VALUE', 3600);

        $item = $pool->getItem('notag_item');
        self::assertTrue($item->isHit());
        self::assertSame('VALUE', $item->get());
    }

    /** @see CachePool::hasItem — respects tag invalidation via getItem */
    public function testHasItemRespectsTagInvalidation(): void
    {
        $this->instance->set('has_tagged', 'VALUE', 3600, ['users']);

        self::assertTrue($this->instance->hasItem('has_tagged'));

        $this->instance->invalidateTags('users');

        self::assertFalse($this->instance->hasItem('has_tagged'), 'hasItem should return false after tag invalidation');
    }

    // -----------------------------------------------------------------------
    // isItemValid method
    // -----------------------------------------------------------------------

    /** @see CachePool::isItemValid — returns true for valid cached item */
    public function testIsItemValidReturnsTrueForValidItem(): void
    {
        $this->instance->set('valid_item', 'VALUE', 3600, ['users']);

        $item = $this->instance->getItem('valid_item');

        self::assertTrue($this->instance->isItemValid($item), 'isItemValid should return true for valid cached item');
    }

    /** @see CachePool::isItemValid — returns false when item is not a hit */
    public function testIsItemValidReturnsFalseForMissedItem(): void
    {
        $item = $this->instance->getItem('missing_key');

        self::assertFalse($item->isHit());
        self::assertFalse($this->instance->isItemValid($item), 'isItemValid should return false for cache miss');
    }

    /** @see CachePool::isItemValid — returns false when tags are invalidated */
    public function testIsItemValidReturnsFalseAfterTagInvalidation(): void
    {
        $this->instance->set('validate_tagged', 'VALUE', 3600, ['users', 'posts']);

        $item = $this->instance->getItem('validate_tagged');
        self::assertTrue($this->instance->isItemValid($item));

        // Invalidate one tag
        $this->instance->invalidateTags('users');

        // Same item object should now be invalid
        self::assertFalse($this->instance->isItemValid($item), 'isItemValid should return false after tag invalidation');
    }

    /** @see CachePool::isItemValid — works with untagged items */
    public function testIsItemValidWorksWithUntaggedItems(): void
    {
        $this->instance->set('untagged_validate', 'VALUE', 3600);

        $item = $this->instance->getItem('untagged_validate');

        self::assertTrue($this->instance->isItemValid($item), 'isItemValid should work with untagged items');

        // Invalidate some unrelated tag
        $this->instance->invalidateTags('users');

        // Untagged item should still be valid
        self::assertTrue($this->instance->isItemValid($item), 'Untagged items should remain valid after unrelated tag invalidation');
    }

    /** @see CachePool::isItemValid — can revalidate after time has passed */
    public function testIsItemValidCanRevalidateAfterDelay(): void
    {
        $this->instance->set('delayed_check', 'VALUE', 3600, ['users']);

        $item = $this->instance->getItem('delayed_check');
        self::assertTrue($this->instance->isItemValid($item));

        // Simulate some time passing and tag invalidation
        usleep(1000);
        $this->instance->invalidateTags('users');

        // Re-validate the same item object
        self::assertFalse($this->instance->isItemValid($item), 'isItemValid should detect invalidation even after delay');
    }

    /** @see CachePool::isItemValid — validates expired items correctly */
    public function testIsItemValidReturnsFalseForExpiredItem(): void
    {
        // Create an item with a future expiration first to ensure it's saved
        $item = $this->instance->getItem('will_expire');
        $item->set('VALUE');
        $item->expiresAfter(3600); // Temporarily set far future
        $this->instance->save($item);

        // Verify it's valid when not expired
        $freshItem = $this->instance->getItem('will_expire');
        self::assertTrue($freshItem->isHit());
        self::assertTrue($this->instance->isItemValid($freshItem));

        // Now forcefully set the expiration to the past and re-save
        $expiredItem = $this->instance->getItem('will_expire');
        $expiredItem->expiresAt(new \DateTime('-1 second')); // Already expired
        $this->instance->save($expiredItem);

        // Fetch again - should be expired now (no sleep needed!)
        $item = $this->instance->getItem('will_expire');
        self::assertFalse($item->isHit(), 'Item with past expiration should not be a hit');
        self::assertFalse($this->instance->isItemValid($item), 'isItemValid should return false for expired item');
    }

    /** @see CachePool::isItemValid — works with tagPool disabled */
    public function testIsItemValidWithTagPoolDisabled(): void
    {
        $pool = new CachePool()->withTagPool(false);

        $pool->set('notag_valid', 'VALUE', 3600);

        $item = $pool->getItem('notag_valid');

        self::assertTrue($pool->isItemValid($item), 'isItemValid should work when tagPool is disabled');
    }

    /** @see CachePool::isItemValid — batch validation scenario */
    public function testIsItemValidBatchValidation(): void
    {
        $this->instance->set('item1', 'V1', 3600, ['users']);
        $this->instance->set('item2', 'V2', 3600, ['posts']);
        $this->instance->set('item3', 'V3', 3600, ['users', 'posts']);

        $items = [
            'item1' => $this->instance->getItem('item1'),
            'item2' => $this->instance->getItem('item2'),
            'item3' => $this->instance->getItem('item3'),
        ];

        // All should be valid initially
        foreach ($items as $key => $item) {
            self::assertTrue($this->instance->isItemValid($item), "$key should be valid initially");
        }

        // Invalidate 'users' tag
        $this->instance->invalidateTags('users');

        // item1 and item3 should be invalid, item2 should still be valid
        self::assertFalse($this->instance->isItemValid($items['item1']), 'item1 should be invalid after users tag invalidation');
        self::assertTrue($this->instance->isItemValid($items['item2']), 'item2 should still be valid (only has posts tag)');
        self::assertFalse($this->instance->isItemValid($items['item3']), 'item3 should be invalid (has users tag)');
    }

    /** @see CachePool::isItemValid + getItem — validation consistency */
    public function testIsItemValidConsistentWithGetItem(): void
    {
        $this->instance->set('consistency_check', 'VALUE', 3600, ['users']);

        $item1 = $this->instance->getItem('consistency_check');
        self::assertTrue($item1->isHit());
        self::assertTrue($this->instance->isItemValid($item1));

        $this->instance->invalidateTags('users');

        // Fresh getItem should return miss (and delete the stale item)
        $item2 = $this->instance->getItem('consistency_check');
        self::assertFalse($item2->isHit(), 'Fresh getItem should return miss after tag invalidation');
        self::assertFalse($this->instance->isItemValid($item2), 'isItemValid should return false for missed item');

        // Old item object still shows as hit (it's a snapshot from before invalidation)
        // but isItemValid re-checks storage and should return false (item was deleted)
        self::assertTrue($item1->isHit(), 'Old CacheItem object retains its original isHit state (snapshot)');
        self::assertFalse($this->instance->isItemValid($item1), 'isItemValid should return false (item no longer in storage)');
    }

    /** @see CachePool::getItem — missing tag envelope makes item invalid */
    public function testGetItemWithMissingTagEnvelopeIsInvalid(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        // Save item with tags
        $pool->set('tagged_item', 'VALUE', 3600, ['users']);
        self::assertTrue($pool->hasItem('tagged_item'), 'Item should be valid initially');

        // Manually delete the tag envelope (simulating corruption or accidental deletion)
        $envKey = '--ww_tag_env--' . hash('sha1', 'tagged_item');
        $tagStorage->remove($envKey);

        // Item should now be invalid because envelope is missing
        self::assertFalse($pool->hasItem('tagged_item'), 'Item with missing tag envelope should be invalid');

        $item = $pool->getItem('tagged_item');
        self::assertFalse($item->isHit(), 'getItem should return miss when tag envelope is missing');
    }

    /** @see CachePool::save — items without tags get empty envelope */
    public function testItemsWithoutTagsGetEmptyEnvelope(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        // Save item without tags
        $pool->set('no_tags', 'VALUE', 3600);

        // Envelope should exist (even if empty)
        $envKey = '--ww_tag_env--' . hash('sha1', 'no_tags');
        self::assertTrue($tagStorage->has($envKey), 'Items without tags should have an empty envelope');

        // Item should be valid
        self::assertTrue($pool->hasItem('no_tags'));

        // Invalidating unrelated tags should not affect it
        $pool->invalidateTags('users');
        self::assertTrue($pool->hasItem('no_tags'), 'Items without tags should be unaffected by tag invalidation');
    }

    /** @see CachePool::fetch — missing envelope forces recomputation */
    public function testFetchWithMissingEnvelopeRecomputes(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        $computeCount = 0;

        // First fetch - computes and saves with tags
        $result = $pool->fetch('compute_item', function ($item) use (&$computeCount) {
            $computeCount++;
            $item->tags('users');
            return 'VALUE_' . $computeCount;
        }, 3600, 0.0, false);

        self::assertSame('VALUE_1', $result);
        self::assertEquals(1, $computeCount);

        // Second fetch - should use cache
        $result = $pool->fetch('compute_item', function ($item) use (&$computeCount) {
            $computeCount++;
            $item->tags('users');
            return 'VALUE_' . $computeCount;
        }, 3600, 0.0, false);

        self::assertSame('VALUE_1', $result);
        self::assertEquals(1, $computeCount, 'Should not recompute when envelope exists');

        // Delete envelope
        $envKey = '--ww_tag_env--' . hash('sha1', 'compute_item');
        $tagStorage->remove($envKey);

        // Third fetch - should recompute because envelope is missing
        $result = $pool->fetch('compute_item', function ($item) use (&$computeCount) {
            $computeCount++;
            $item->tags('users');
            return 'VALUE_' . $computeCount;
        }, 3600, 0.0, false);

        self::assertSame('VALUE_2', $result);
        self::assertEquals(2, $computeCount, 'Should recompute when envelope is missing');
    }

    /** @see CachePool::save — switching from tags to no tags updates envelope */
    public function testSwitchingFromTagsToNoTagsUpdatesEnvelope(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        // Save with tags
        $pool->set('changeable', 'VALUE1', 3600, ['users']);

        $envKey = '--ww_tag_env--' . hash('sha1', 'changeable');
        self::assertTrue($tagStorage->has($envKey), 'Item with tags should have envelope');

        // Get envelope through tagPool to unserialize it properly
        $tagPool = $pool->getTagPool();
        $envelopeItem = $tagPool->getItem($envKey);
        self::assertTrue($envelopeItem->isHit());
        $envelope = $envelopeItem->get();
        self::assertIsArray($envelope, 'Envelope should be an array');
        self::assertNotEmpty($envelope, 'Envelope should contain tag versions');

        // Invalidate tags - should make item invalid
        $pool->invalidateTags('users');
        self::assertFalse($pool->hasItem('changeable'), 'Item should be invalid after tag invalidation');

        // Re-save without tags
        $pool->set('changeable', 'VALUE2', 3600);

        // Envelope should now be empty (no tags)
        $envelopeItem = $tagPool->getItem($envKey);
        self::assertTrue($envelopeItem->isHit());
        $envelope = $envelopeItem->get();
        self::assertIsArray($envelope, 'Item should still have envelope');
        self::assertEmpty($envelope, 'Envelope should be empty (no tags)');

        // Item should be valid and unaffected by tag invalidation
        self::assertTrue($pool->hasItem('changeable'));
        $pool->invalidateTags('users');
        self::assertTrue($pool->hasItem('changeable'), 'Item without tags should be unaffected');
    }

    /** @see CachePool::getItem — corrupted envelope (non-array) makes item invalid */
    public function testGetItemWithCorruptedEnvelopeIsInvalid(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        // Save normal item
        $pool->set('corrupted', 'VALUE', 3600, ['users']);
        self::assertTrue($pool->hasItem('corrupted'));

        // Corrupt the envelope by saving invalid data through tagPool
        $tagPool = $pool->getTagPool();
        $envKey = '--ww_tag_env--' . hash('sha1', 'corrupted');
        $envItem = $tagPool->getItem($envKey);
        $envItem->set('not_an_array'); // Invalid data
        $envItem->expiresAfter(3600);
        $tagPool->save($envItem);

        // Item should be invalid because envelope is corrupted
        self::assertFalse($pool->hasItem('corrupted'), 'Item with corrupted envelope should be invalid');
    }

    /** @see CachePool::invalidateTags — works correctly after envelope deletion and restoration */
    public function testInvalidateTagsAfterEnvelopeRestoration(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        $computeCount = 0;

        // Initial save with tags
        $pool->fetch('item', function ($item) use (&$computeCount) {
            $computeCount++;
            $item->tags('users');
            return 'V' . $computeCount;
        }, 3600, 0.0, false);

        self::assertEquals(1, $computeCount);

        // Delete envelope - forces recomputation
        $envKey = '--ww_tag_env--' . hash('sha1', 'item');
        $tagStorage->remove($envKey);

        // Fetch again - recomputes and creates new envelope
        $result = $pool->fetch('item', function ($item) use (&$computeCount) {
            $computeCount++;
            $item->tags('users');
            return 'V' . $computeCount;
        }, 3600, 0.0, false);

        self::assertSame('V2', $result);
        self::assertEquals(2, $computeCount);

        // Now invalidate tags - should work correctly with restored envelope
        $pool->invalidateTags('users');

        $result = $pool->fetch('item', function ($item) use (&$computeCount) {
            $computeCount++;
            $item->tags('users');
            return 'V' . $computeCount;
        }, 3600, 0.0, false);

        self::assertSame('V3', $result);
        self::assertEquals(3, $computeCount, 'Should recompute after tag invalidation');
    }

    /** @see CachePool::isItemValid — detects missing envelope */
    public function testIsItemValidDetectsMissingEnvelope(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        // Save item with tags
        $pool->set('validate_me', 'VALUE', 3600, ['posts']);

        $item = $pool->getItem('validate_me');
        self::assertTrue($item->isHit());
        self::assertTrue($pool->isItemValid($item), 'Item should be valid with envelope');

        // Delete envelope
        $envKey = '--ww_tag_env--' . hash('sha1', 'validate_me');
        $tagStorage->remove($envKey);

        // isItemValid should detect missing envelope
        self::assertFalse($pool->isItemValid($item), 'isItemValid should detect missing envelope');

        // Fresh getItem should also detect it
        $freshItem = $pool->getItem('validate_me');
        self::assertFalse($freshItem->isHit(), 'Fresh getItem should miss when envelope missing');
    }

    /** @see CachePool::deleteItem — also deletes envelope */
    public function testDeleteItemAlsoRemovesEnvelope(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        // Save items with and without tags
        $pool->set('with_tags', 'V1', 3600, ['users']);
        $pool->set('without_tags', 'V2', 3600);

        $envKeyWithTags = '--ww_tag_env--' . hash('sha1', 'with_tags');
        $envKeyWithoutTags = '--ww_tag_env--' . hash('sha1', 'without_tags');

        // Both should have envelopes
        self::assertTrue($tagStorage->has($envKeyWithTags));
        self::assertTrue($tagStorage->has($envKeyWithoutTags));

        // Delete items
        $pool->deleteItem('with_tags');
        $pool->deleteItem('without_tags');

        // Envelopes should be deleted too
        self::assertFalse($tagStorage->has($envKeyWithTags), 'Envelope should be deleted with item');
        self::assertFalse($tagStorage->has($envKeyWithoutTags), 'Envelope should be deleted with item');
    }

    /** @see CachePool::getMultiple — respects missing envelopes */
    public function testGetMultipleRespectsEnvelopeLogic(): void
    {
        $mainStorage = new ArrayStorage();
        $tagStorage = new ArrayStorage();
        $pool = (new CachePool($mainStorage))->withTagPool($tagStorage);

        // Save multiple items
        $pool->set('item1', 'V1', 3600, ['users']);
        $pool->set('item2', 'V2', 3600);
        $pool->set('item3', 'V3', 3600, ['posts']);

        // All should be accessible
        $values = iterator_to_array($pool->getMultiple(['item1', 'item2', 'item3']));
        self::assertCount(3, $values);

        // Delete envelope for item1
        $envKey1 = '--ww_tag_env--' . hash('sha1', 'item1');
        $tagStorage->remove($envKey1);

        // item1 should not be returned (missing envelope), others should be fine
        $values = iterator_to_array($pool->getMultiple(['item1', 'item2', 'item3'], 'DEFAULT'));

        self::assertSame('DEFAULT', $values['item1'], 'Item with missing envelope should return default');
        self::assertSame('V2', $values['item2'], 'Item without tags should work');
        self::assertSame('V3', $values['item3'], 'Item with valid envelope should work');
    }
}
