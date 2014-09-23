<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\NinCompare;

/**
 * Test class of NinCompare
 *
 * @since {DEPLOY_VERSION}
 */
class NinCompareTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var NinCompare
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
		$this->instance = new NinCompare('sakura', array('rose', 'sunflower', 'sakura'));
	}

	/**
	 * Method to test compare().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\InCompare::compare
	 */
	public function testCompare()
	{
		$this->assertFalse($this->instance->compare());

		$compare = new NinCompare('1', array(1, 2, 3, 4, 5));

		$this->assertFalse($compare->compare());
		$this->assertTrue($compare->compare(true));
	}

	/**
	 * Method to test toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\InCompare::toString
	 */
	public function testToString()
	{
		$this->assertEquals('sakura NOT IN (rose,sunflower,sakura)', $this->instance->toString());
		$this->assertEquals('`sakura` NOT IN ("rose","sunflower","sakura")', $this->instance->toString('`', '"'));
	}
}
