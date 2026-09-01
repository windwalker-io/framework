<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\RedisStorage;
use Windwalker\Cache\Storage\TouchableStorageInterface;
use Windwalker\Utilities\Env;

/**
 * The RedisStorageTest class.
 */
class RedisStorageTest extends AbstractStorageTestCase
{
    /**
     * @var RedisStorage
     */
    protected $instance;

    protected \Redis $redis;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        if (!Env::get('REDIS_ENABLED')) {
            self::markTestSkipped('Redis not enabled');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = new \Redis();
        $port = (int) ($_SERVER['REDIS_PORT'] ?? $_ENV['REDIS_PORT'] ?? 6379);
        $this->redis->connect('127.0.0.1', $port);

        $this->instance = new RedisStorage($this->redis);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testImplementsGroupedStorageInterface(): void
    {
        self::assertInstanceOf(GroupedStorageInterface::class, $this->instance);
    }

    public function testWithGroupCreatesScopedClone(): void
    {
        $flower = $this->instance->withGroup('flower');
        $tree = $this->instance->withGroup('tree');

        self::assertNotSame($flower, $tree);
        self::assertSame('', $this->instance->group);
        self::assertSame('flower', $flower->group);
        self::assertSame('tree', $tree->group);

        $flower->save('same-key', 'FLOWER', time() + 60);
        $tree->save('same-key', 'TREE', time() + 60);

        self::assertSame('FLOWER', $flower->get('same-key'));
        self::assertSame('TREE', $tree->get('same-key'));
        self::assertNull($this->instance->get('same-key'));
    }

    public function testImplementsTouchableStorageInterface(): void
    {
        self::assertInstanceOf(TouchableStorageInterface::class, $this->instance);
    }

    /**
     * @see  RedisStorage::updateExpiration
     */
    public function testUpdateExpirationUpdatesTtlAndReturnsTrue(): void
    {
        $this->instance->save('foo', 'FOO', time() + 60);

        $newExpiration = time() + 3600;

        self::assertTrue($this->instance->updateExpiration('foo', $newExpiration));
        self::assertEqualsWithDelta($newExpiration, time() + $this->redis->ttl('foo'), 2);
        self::assertSame('FOO', $this->instance->get('foo'));
    }

    /**
     * @see  RedisStorage::updateExpiration — returns false when the key does not exist
     */
    public function testUpdateExpirationReturnsFalseWhenKeyNotFound(): void
    {
        self::assertFalse($this->instance->updateExpiration('missing', time() + 60));
    }

    /**
     * @see  RedisStorage::updateExpiration — expiration=0 removes the TTL (never expires)
     */
    public function testUpdateExpirationWithZeroPersistsKey(): void
    {
        $this->instance->save('foo', 'FOO', time() + 10);

        self::assertTrue($this->instance->updateExpiration('foo', 0));

        self::assertSame(-1, $this->redis->ttl('foo'));
        self::assertSame('FOO', $this->instance->get('foo'));
    }

    /**
     * @see  RedisStorage::updateExpiration — an expired timestamp makes item unreadable
     */
    public function testUpdateExpirationToPastMakesItemUnreadable(): void
    {
        $this->instance->save('foo', 'FOO', time() + 60);

        self::assertTrue($this->instance->updateExpiration('foo', time() - 10));

        self::assertFalse($this->instance->has('foo'));
        self::assertNull($this->instance->get('foo'));
    }
}
