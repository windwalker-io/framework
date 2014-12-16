<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Application\Test;

use Windwalker\Application\Test\Stub\StubDeamon;

/**
 * Test class of AbstractDaemonApplication
 *
 * @since 2.0
 */
class AbstractDaemonApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubDeamon
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
		// Skip this test suite if PCNTL extension is not available
		if (!extension_loaded('PCNTL'))
		{
			$this->markTestSkipped('The PCNTL extension is not available.');
		}

		// $this->instance = new StubDeamon;
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
	 * Method to test signal().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::signal
	 * @TODO   Implement testSignal().
	 */
	public function testSignal()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test isActive().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::isActive
	 * @TODO   Implement testIsActive().
	 */
	public function testIsActive()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test loadConfiguration().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::loadConfiguration
	 * @TODO   Implement testLoadConfiguration().
	 */
	public function testLoadConfiguration()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test execute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::execute
	 * @TODO   Implement testExecute().
	 */
	public function testExecute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test restart().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::restart
	 * @TODO   Implement testRestart().
	 */
	public function testRestart()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test stop().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::stop
	 * @TODO   Implement testStop().
	 */
	public function testStop()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::getName
	 * @TODO   Implement testGetName().
	 */
	public function testGetName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractDaemonApplication::setName
	 * @TODO   Implement testSetName().
	 */
	public function testSetName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
