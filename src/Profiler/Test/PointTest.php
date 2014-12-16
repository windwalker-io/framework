<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Profiler\Test;

use Windwalker\Profiler\Point\Point;

/**
 * Test class of Point
 *
 * @since 2.0
 */
class PointTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Point
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
		$this->instance = new Point('foo');
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
	 * Method to test getName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Profiler\Point\Point::getName
	 */
	public function testGetName()
	{
		$this->assertEquals($this->instance->getName(), 'foo');
	}

	/**
	 * Method to test getTime().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Profiler\Point\Point::getTime
	 */
	public function testGetTime()
	{
		$profilePoint = new Point('test', 0, 0);

		$this->assertEquals(0, $profilePoint->getTime());

		$profilePoint = new Point('test', 1.5, 0);

		$this->assertEquals(1.5, $profilePoint->getTime());
	}

	/**
	 * Method to test getMemory().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Profiler\Point\Point::getMemory
	 */
	public function testGetMemory()
	{
		$profilePoint = new Point('test', 0, 0);

		$this->assertEquals(0, $profilePoint->getMemory());

		$profilePoint = new Point('test', 0, 456895);

		$this->assertEquals(456895, $profilePoint->getMemory());
	}
}
