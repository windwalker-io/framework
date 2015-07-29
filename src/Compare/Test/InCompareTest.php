<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\InCompare;

/**
 * Test class of InCompare
 *
 * @since 2.0
 */
class InCompareTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var InCompare
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
		$this->instance = new InCompare('sakura', array('rose', 'sunflower', 'sakura'));
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
		$this->assertTrue($this->instance->compare());

		$compare = new InCompare('1', array(1, 2, 3, 4, 5));

		$this->assertTrue($compare->compare());
		$this->assertFalse($compare->compare(true));
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
		$this->assertEquals('sakura IN (rose,sunflower,sakura)', $this->instance->toString());
		$this->assertEquals('`sakura` IN ("rose","sunflower","sakura")', $this->instance->toString('`', '"'));
	}
}
