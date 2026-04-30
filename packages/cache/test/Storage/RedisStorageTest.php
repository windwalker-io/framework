<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\RedisStorage;
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

        $redis = new \Redis();
        $port = (int) ($_SERVER['REDIS_PORT'] ?? $_ENV['REDIS_PORT'] ?? 6379);
        $redis->connect('127.0.0.1', $port);

        $this->instance = new RedisStorage($redis);
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
}
