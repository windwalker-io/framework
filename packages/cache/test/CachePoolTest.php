<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test;

use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Windwalker\Cache\CacheItem;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\JsonAssocSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\StorageInterface;
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
        $storageMock->expects($this->once())
            ->method('save')
            ->with(
                'foo',
                'Flower',
                self::callback(static fn(int $expiration) => abs($expiration - (time() + 30)) <= 1)
            )
            ->willReturn(true);
        $storageMock->expects($this->once())
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
        $storageMock->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(
                static fn(string $key): bool => match (true) {
                    $key === 'flower' => true,
                    self::isItemMetadataKey($key) => false,
                    default => false,
                }
            );
        $storageMock->expects($this->once())
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
        $storageMock->expects($this->exactly(2))
            ->method('has')
            ->with('flip')
            ->willReturnCallback(static function () use (&$hasCalls): bool {
                $hasCalls++;

                return $hasCalls === 1;
            });
        $storageMock->expects($this->once())
            ->method('get')
            ->with('flip')
            ->willReturn(null);

        $pool = (new CachePool())->withStorage($storageMock)->withTagPool(false);
        $item = $pool->getItem('flip');

        self::assertFalse($item->isHit());
        self::assertNull($item->get());
    }

    /** @see CachePool::getItem — expired metadata results in cache miss (lazy deletion) */
    public function testGetItemDeletesStaleEntryWhenMetadataIsExpired(): void
    {
        $meta = serialize(
            [
                'realExpiry' => microtime(true) - 10,
                'ctime' => 1,
            ]
        );

        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(
                static fn(string $key): bool => match (true) {
                    $key === 'stale' => true,
                    self::isItemMetadataKey($key) => true,
                    default => false,
                }
            );
        $storageMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(
                static fn(string $key): string => match (true) {
                    $key === 'stale' => 'VALUE',
                    self::isItemMetadataKey($key) => $meta,
                    default => '',
                }
            );

        // With lazy deletion, getItem should NOT call remove()
        $storageMock->expects($this->never())
            ->method('remove');

        $pool = new CachePool()->withStorage($storageMock)->withTagPool(false);
        $item = $pool->getItem('stale');

        // Item should be marked as miss due to expired metadata
        self::assertFalse($item->isHit(), 'Expired item should be marked as miss');
        // But the item remains in storage (lazy deletion - storage will clean it up later)
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
        $storageMock->expects($this->exactly(2))
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
        $storageMock->expects($this->once())
            ->method('remove')
            ->with('hello')
            ->willThrowException(new RuntimeException());

        $this->instance = $this->instance->withStorage($storageMock);

        self::assertFalse($this->instance->deleteItem('hello'));
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
        $tree = $pool->withGroup('tree');

        self::assertNotSame($pool, $flower);
        self::assertNotSame($flower, $tree);

        self::assertInstanceOf(GroupedStorageInterface::class, $flower->getStorage());
        self::assertSame('flower', $flower->getStorage()->group);
        self::assertSame('tree', $tree->getStorage()->group);

        $flower->set('same-key', 'FLOWER');
        $tree->set('same-key', 'TREE');

        self::assertSame('FLOWER', $flower->get('same-key'));
        self::assertSame('TREE', $tree->get('same-key'));
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
}
