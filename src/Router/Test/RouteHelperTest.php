<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test;

use Windwalker\Router\RouteHelper;

/**
 * Test class of RouteHelper
 *
 * @since 2.0
 */
class RouteHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to test sanitize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\RouteHelper::sanitize
	 */
	public function testSanitize()
	{
		$this->assertEquals('/foo/bar/baz', RouteHelper::sanitize('/foo/bar/baz'));
		$this->assertEquals('/foo/bar/baz', RouteHelper::sanitize('http://flower.com/foo/bar/baz/?olive=peace'));
	}

	/**
	 * Method to test normalise()
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Router\RouteHelper::normalise
	 */
	public function testNormalise()
	{
		$this->assertEquals('/foo/bar/baz', RouteHelper::sanitize('foo/bar/baz/'));
	}

	/**
	 * testGetVariables
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Router\RouteHelper::getVariables
	 */
	public function testGetVariables()
	{
		$array = array(
			0 => 5,
			'id' => 5,
			1 => 'foo',
			'bar' => 'foo'
		);

		$this->assertEquals(array('id' => 5, 'bar' => 'foo'), RouteHelper::getVariables($array));

		$vars = array(
			'flower' => 'sakura'
		);

		$this->assertEquals(array('flower' => 'sakura', 'id' => 5, 'bar' => 'foo'), RouteHelper::getVariables($array, $vars));
	}
}
