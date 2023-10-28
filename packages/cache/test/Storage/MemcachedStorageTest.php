<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Cache\Storage\MemcachedStorage;
use Windwalker\Utilities\Env;

/**
 * The MemcachedStorageTest class.
 */
class MemcachedStorageTest extends AbstractStorageTestCase
{
    /**
     * @var MemcachedStorage
     */
    protected $instance;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        if (!Env::get('MEMCACHED_ENABLED')) {
            self::markTestSkipped('Memcached not supported');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new MemcachedStorage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
