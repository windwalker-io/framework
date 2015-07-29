<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test\Matcher;

use Windwalker\Router\Matcher\BinaryMatcher;
use Windwalker\Router\Route;

/**
 * Test class of BinaryMatcher
 *
 * @since 2.0
 */
class BinaryMatcherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var BinaryMatcher
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
		$this->instance = new BinaryMatcher;
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
	 * Method to test match().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Matcher\BinaryMatcher::match
	 */
	public function testMatch()
	{
		$routes = file_get_contents(__DIR__ . '/../fixtures/routes.txt');

		$routes = explode("\n", trim($routes));

		$routes = array_map(
			function ($route)
			{
				return new Route($route, $route, array('_return' => $route));
			},
			$routes
		);

		$matched = $this->instance->setRoutes($routes)
			->match('/corge/quux/qux');

		$this->assertEquals('/corge/quux/qux', $matched->getName());

		$this->instance->getCount();
	}
}
