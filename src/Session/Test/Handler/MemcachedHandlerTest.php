<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Session\Test\Handler;

use Windwalker\Session\Handler\MemcachedHandler;

/**
 * Test class of MemcachedHandler
 *
 * @since 2.0
 */
class MemcachedHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var MemcachedHandler
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        if (!MemcachedHandler::isSupported()) {
            $this->markTestSkipped('Memcached storage is not enabled on this system.');
        }

        $this->instance = new MemcachedHandler();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * Method to test isSupported().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\MemcachedHandler::isSupported
     */
    public function testIsSupported()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test open().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\MemcachedHandler::open
     * @TODO   Implement testOpen().
     */
    public function testOpen()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test close().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\MemcachedHandler::close
     * @TODO   Implement testClose().
     */
    public function testClose()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test read().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\MemcachedHandler::read
     * @TODO   Implement testRead().
     */
    public function testRead()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test write().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\MemcachedHandler::write
     * @TODO   Implement testWrite().
     */
    public function testWrite()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test destroy().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\MemcachedHandler::destroy
     * @TODO   Implement testDestroy().
     */
    public function testDestroy()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test gc().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\MemcachedHandler::gc
     * @TODO   Implement testGc().
     */
    public function testGc()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
