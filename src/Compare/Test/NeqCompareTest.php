<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\NeqCompare;

/**
 * Test class of NeqCompare
 *
 * @since {DEPLOY_VERSION}
 */
class NeqCompareTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var NeqCompare
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
		$this->instance = new NeqCompare('flower', 'sakura');
	}

	/**
	 * testToString
	 *
	 * @return  void
	 */
	public function testToString()
	{
		$this->assertEquals('flower != sakura', $this->instance->toString());
	}

	/**
	 * testToString
	 *
	 * @return  void
	 */
	public function testCompare()
	{
		$compare = new NeqCompare(1, '1');

		$this->assertFalse($compare->compare());
		$this->assertTrue($compare->compare(true));
	}
}
