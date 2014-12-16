<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Test\Handler;

use Windwalker\Session\Handler\WincacheHandler;

/**
 * Test class of WincacheHandler
 *
 * @since 2.0
 */
class WincacheHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var WincacheHandler
	 */
	protected $instance;

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'PHPSESSID';

	/**
	 * Property id.
	 *
	 * @var  string
	 */
	protected $id = '';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		// Skip these tests if WinCache isn't available.
		if (!WincacheHandler::isSupported())
		{
			$this->markTestSkipped('WinCache storage is not enabled on this system.');
		}

		$this->instance = new WincacheHandler;

		$this->id = session_id(md5($this->name));

		session_name($this->name);

		parent::setUp();
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
	 * @covers Windwalker\Session\Handler\WincacheHandler::isSupported
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
	 * Method to test register().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Handler\WincacheHandler::register
	 * @TODO   Implement testRegister().
	 */
	public function testRegister()
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
	 * @covers Windwalker\Session\Handler\WincacheHandler::open
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
	 * @covers Windwalker\Session\Handler\WincacheHandler::close
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
	 * @covers Windwalker\Session\Handler\WincacheHandler::read
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
	 * @covers Windwalker\Session\Handler\WincacheHandler::write
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
	 * @covers Windwalker\Session\Handler\WincacheHandler::destroy
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
	 * @covers Windwalker\Session\Handler\WincacheHandler::gc
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
