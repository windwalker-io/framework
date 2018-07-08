<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Test\Handler;

use Windwalker\Session\Handler\XcacheHandler;

/**
 * Test class of XcacheHandler
 *
 * @since 2.0
 */
class XcacheHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var XcacheHandler
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
        if (!XcacheHandler::isSupported()) {
            $this->markTestSkipped('XCache is not enabled on this system.');
        }

        $this->instance = new XcacheHandler();
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
     * @covers \Windwalker\Session\Handler\XcacheHandler::isSupported
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
     * Method to test read().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\XcacheHandler::read
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
     * @covers \Windwalker\Session\Handler\XcacheHandler::write
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
     * @covers \Windwalker\Session\Handler\XcacheHandler::destroy
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
     * Method to test open().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\XcacheHandler::open
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
     * @covers \Windwalker\Session\Handler\XcacheHandler::close
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
     * Method to test gc().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\XcacheHandler::gc
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
