<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Tests\Driver;

use Windwalker\Database\Driver\Pdo\PdoDriver;

/**
 * Test class of PdoDriver
 *
 * @since {DEPLOY_VERSION}
 */
class PdoDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test instance.
     *
     * @var PdoDriver
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
		$opt = [
			'user' => 'root',
			'password' => '1234',
			'driver' => 'mysql',
			'database' => 'acme'
		];

        $this->instance = new PdoDriver(null, $opt);
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
     * Method to test connect().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::connect
     * @TODO   Implement testConnect().
     */
    public function testConnect()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test disconnect().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::disconnect
     * @TODO   Implement testDisconnect().
     */
    public function testDisconnect()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getOption().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getOption
     * @TODO   Implement testGetOption().
     */
    public function testGetOption()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setOption().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::setOption
     * @TODO   Implement testSetOption().
     */
    public function testSetOption()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getVersion().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getVersion
     * @TODO   Implement testGetVersion().
     */
    public function testGetVersion()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test select().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::select
     * @TODO   Implement testSelect().
     */
    public function testSelect()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setQuery().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::setQuery
     * @TODO   Implement testSetQuery().
     */
    public function testSetQuery()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test doExecute().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::doExecute
     * @TODO   Implement testDoExecute().
     */
    public function testDoExecute()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test freeResult().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::freeResult
     * @TODO   Implement testFreeResult().
     */
    public function testFreeResult()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getQuery().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getQuery
     * @TODO   Implement testGetQuery().
     */
    public function testGetQuery()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getTable().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getTable
     * @TODO   Implement testGetTable().
     */
    public function testGetTable()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getDatabase().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getDatabase
     * @TODO   Implement testGetDatabase().
     */
    public function testGetDatabase()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getReader().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getReader
     * @TODO   Implement testGetReader().
     */
    public function testGetReader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getWriter().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getWriter
     * @TODO   Implement testGetWriter().
     */
    public function testGetWriter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getTransaction().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getTransaction
     * @TODO   Implement testGetTransaction().
     */
    public function testGetTransaction()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test listDatabases().
     *
     * @return void
     *
     * @covers Windwalker\Database\Driver\Pdo\PdoDriver::listDatabases
     * @TODO   Implement testListDatabases().
     */
    public function testListDatabases()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
