<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Windwalker\Cache\CacheItem;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\JsonAssocSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\StorageInterface;
use Windwalker\Test\Traits\TestAccessorTrait;

/**
 * The CachePoolTest class.
 */
class CachePoolTest extends TestCase
{
    use MockeryPHPUnitIntegration;
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
     * @see  CachePool::setSerializer
     */
    public function testGetSetSerializer(): void
    {
        $this->instance->setSerializer($ser = new RawSerializer());

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

        $storageMock = Mockery::mock(StorageInterface::class)
            ->shouldReceive('save')
            ->once()
            ->with('foo', 'Flower', time() + 30)
            ->getMock();

        $this->instance->setStorage($storageMock);
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
        $storageMock = Mockery::mock(StorageInterface::class);
        $storageMock->shouldReceive('get')
            ->with('flower')
            ->andReturn('Sakura');

        $storageMock->shouldReceive('has')
            ->with('flower')
            ->andReturn(true);

        $this->instance->setStorage($storageMock);

        $item = $this->instance->getItem('flower');

        self::assertEquals('Sakura', $item->get());
        self::assertTrue($item->isHit());
    }

    public function testGetItemWithSerializer(): void
    {
        $this->instance->setSerializer(new JsonAssocSerializer());

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
        $storageMock = Mockery::mock(StorageInterface::class);
        $storageMock->shouldReceive('remove')
            ->with('hello')
            ->andReturn(true);

        $this->instance->setStorage($storageMock);

        $this->instance->deleteItem('hello');

        $storageMock = Mockery::mock(StorageInterface::class);
        $storageMock->shouldReceive('remove')
            ->with('hello')
            ->andThrow(RuntimeException::class);

        $this->instance->setStorage($storageMock);

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
     * @see  CachePool::setStorage / getStorage
     */
    public function testSetStorage(): void
    {
        $storage = new ArrayStorage();

        $this->instance->setStorage($storage);

        self::assertSame($storage, $this->instance->getStorage());
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

    public function testGetWithSerializer(): void
    {
        $this->instance->setSerializer(new JsonAssocSerializer());

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
        $result = $this->instance->call('alias_key', static fn () => 'legacy', 60);
        self::assertEquals('legacy', $result);

        // With lock=true (old explicit opt-in)
        $result = $this->instance->call('alias_key2', static fn () => 'locked', 60, true);
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
        $storage    = new ArrayStorage();
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
}
