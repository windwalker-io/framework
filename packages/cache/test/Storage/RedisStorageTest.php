<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Cache\Storage\RedisStorage;
use Windwalker\Utilities\Env;

/**
 * The RedisStorageTest class.
 */
class RedisStorageTest extends AbstractStorageTest
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
}
