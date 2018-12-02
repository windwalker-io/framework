<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Session\Test\Handler;

use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\Session\Database\WindwalkerAdapter;
use Windwalker\Session\Handler\DatabaseHandler;

/**
 * Test class of DatabaseHandler
 *
 * @since 2.0
 */
class DatabaseHandlerTest extends AbstractDatabaseTestCase
{
    /**
     * Property driver.
     *
     * @var string
     */
    protected static $driver = 'mysql';

    /**
     * Test instance.
     *
     * @var DatabaseHandler
     */
    protected $instance;

    /**
     * setUpBeforeClass
     *
     * @return  void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$dbo->getTable('windwalker_sessions')->save(
            function (Schema $schema) {
                $schema->varchar('id')->allowNull(false);
                $schema->text('data');
                $schema->varchar('time');
            }
        );
    }

    /**
     * tearDownAfterClass
     *
     * @return  void
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    /**
     * Method to test open().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\DatabaseHandler::open
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
     * @covers \Windwalker\Session\Handler\DatabaseHandler::close
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
     * @throws \Exception
     * @covers \Windwalker\Session\Handler\DatabaseHandler::read
     */
    public function testReadAndWrite()
    {
        $this->instance->write('id', 'foo');

        $this->assertEquals('foo', $this->instance->read('id'));
    }

    /**
     * Method to test destroy().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\DatabaseHandler::destroy
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
     * @covers \Windwalker\Session\Handler\DatabaseHandler::gc
     * @TODO   Implement testGc().
     */
    public function testGc()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test isSupported().
     *
     * @return void
     *
     * @covers \Windwalker\Session\Handler\DatabaseHandler::isSupported
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
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->instance = new DatabaseHandler(new WindwalkerAdapter(static::$dbo));
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
}
