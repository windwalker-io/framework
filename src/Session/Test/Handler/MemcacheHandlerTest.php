<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Test\Handler;

use Windwalker\Session\Handler\MemcacheHandler;

/**
 * Test class of MemcacheHandler
 *
 * @since 2.0
 */
class MemcacheHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var MemcacheHandler
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
        if (!MemcacheHandler::isSupported()) {
            $this->markTestSkipped('Memcache is not enabled on this system.');
        }

        $this->instance = new MemcacheHandler();
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
     * @covers \Windwalker\Session\Handler\MemcacheHandler::isSupported
     * @TODO   Implement testIsSupported().
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
     * @covers \Windwalker\Session\Handler\MemcacheHandler::open
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
     * @covers \Windwalker\Session\Handler\MemcacheHandler::close
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
     * @covers \Windwalker\Session\Handler\MemcacheHandler::read
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
     * @covers \Windwalker\Session\Handler\MemcacheHandler::write
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
     * @covers \Windwalker\Session\Handler\MemcacheHandler::destroy
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
     * @covers \Windwalker\Session\Handler\MemcacheHandler::gc
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
