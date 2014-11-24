<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\EqCompare;

/**
 * Test class of EqCompare
 *
 * @since {DEPLOY_VERSION}
 */
class EqCompareTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var EqCompare
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
		$this->instance = new EqCompare('flower', 'sakura');
	}

	/**
	 * testToString
	 *
	 * @return  void
	 */
	public function testToString()
	{
		$this->assertEquals('flower = sakura', $this->instance->toString());
	}

	/**
	 * testToString
	 *
	 * @return  void
	 */
	public function testCompare()
	{
		$compare = new EqCompare(1, '1');

		$this->assertTrue($compare->compare());
		$this->assertFalse($compare->compare(true));
	}
}
