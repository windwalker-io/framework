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
     * @see  CachePool::setStorage
     */
    public function testSetStorage(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
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
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::deleteMultiple
     */
    public function testDeleteMultiple(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::setMultiple
     */
    public function testSetMultiple(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::delete
     */
    public function testDelete(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::has
     */
    public function testHas(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::__destruct
     */
    public function testDestruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::getStorage
     */
    public function testGetStorage(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
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
