<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test;

use Windwalker\Router\SingleActionRouter;

/**
 * Test class of SingleActionRouter
 *
 * @since 2.0
 */
class SingleActionRouterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var SingleActionRouter
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
		$this->instance = new SingleActionRouter;
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
	 * Method to test addMap().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\SingleActionRouter::addMap
	 */
	public function testAddMap()
	{
		$this->instance->addMap('flower/(id)/(alias)', 'FlowerController');

		$routes = $this->instance->getRoutes();

		$this->assertInstanceOf('Windwalker\Router\Route', $routes[0]);
		$this->assertEquals('/flower/(id)/(alias)', $routes[0]->getPattern());
	}

	/**
	 * Method to test match().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\SingleActionRouter::match
	 */
	public function testMatch()
	{
		$routes = array(
			'flower/(id)/(alias)' => 'FlowerController',
			'foo/bar(/id,sakura)' => 'SakuraController'
		);

		$this->instance->addMaps($routes);

		$result = $this->instance->match('flower/5/foo');
		$vars = $this->instance->getVariables();

		$this->assertEquals('FlowerController', $result);
		$this->assertEquals('foo', $vars['alias']);

		$result = $this->instance->match('foo/bar/5/baz');
		$vars = $this->instance->getVariables();

		$this->assertEquals('SakuraController', $result);
		$this->assertEquals('baz', $vars['sakura']);
	}

	/**
	 * Method to test getRequests().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\SingleActionRouter::getVariables
	 * @TODO   Implement testGetVariables().
	 */
	public function testGetVariables()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setRequests().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\SingleActionRouter::setVariables
	 * @TODO   Implement testSetVariables().
	 */
	public function testSetVariables()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
