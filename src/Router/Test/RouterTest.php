<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test;

use Windwalker\Router\Matcher\TrieMatcher;
use Windwalker\Router\Route;
use Windwalker\Router\Router;

/**
 * Test class of Router
 *
 * @since 2.0
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Router
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
		$this->instance = new Router;
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
	 * @covers Windwalker\Router\Router::addMap
	 */
	public function testAddMap()
	{
		$this->instance->addMap('flower/(id)/(alias)', array('_controller' => 'FlowerController'));

		$routes = $this->instance->getRoutes();

		$this->assertInstanceOf('Windwalker\Router\Route', $routes[0]);
		$this->assertEquals('/flower/(id)/(alias)', $routes[0]->getPattern());
	}

	/**
	 * Method to test addMaps().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::addMaps
	 */
	public function testAddMaps()
	{
		$routes = array(
			'flower/(id)/(alias)' => array('_controller' => 'FlowerController'),
			'flower/(id)/sakura' => array('_controller' => 'SakuraController'),
		);

		$this->instance->addMaps($routes);

		$routes = $this->instance->getRoutes();

		$this->assertInstanceOf('Windwalker\Router\Route', $routes[0]);
		$this->assertInstanceOf('Windwalker\Router\Route', $routes[1]);
	}

	/**
	 * Method to test addRoute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::addRoute
	 */
	public function testAddRoute()
	{
		$this->instance->addRoute(new Route(null, 'flower/(id)/(alias)', array('_controller' => 'FlowerController')));

		$routes = $this->instance->getRoutes();

		$this->assertInstanceOf('Windwalker\Router\Route', $routes[0]);

		$result = $this->instance->match('flower/5/foo');

		$this->assertInstanceOf('Windwalker\Router\Route', $result);

		$result = $result->getVariables();

		$this->assertEquals('FlowerController', $result['_controller']);
		$this->assertEquals('foo', $result['alias']);

		$this->instance->addRoute(new Route('sakura', 'flower/(id)/sakura', array('_controller' => 'SakuraController')));

		$routes = $this->instance->getRoutes();

		$this->assertInstanceOf('Windwalker\Router\Route', $routes['sakura']);

		$this->instance->addRoute('foo', 'foo/bar/baz', array('_ctrl' => 'yoo'));

		$routes = $this->instance->getRoutes();

		$this->assertInstanceOf('Windwalker\Router\Route', $routes['foo']);
	}

	/**
	 * Method to test addRoutes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::addRoutes
	 */
	public function testAddRoutes()
	{
		$routes = array(
			new Route(null, 'flower/(id)/(alias)', array('_controller' => 'FlowerController')),
			new Route('sakura', 'flower/(id)/sakura', array('_controller' => 'SakuraController')),
		);

		$this->instance->addRoutes($routes);

		$routes = $this->instance->getRoutes();

		$this->assertInstanceOf('Windwalker\Router\Route', $routes[0]);
		$this->assertInstanceOf('Windwalker\Router\Route', $routes['sakura']);
	}

	/**
	 * testHasAndGetRoute
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Router\Router::hasRoutes
	 * @covers Windwalker\Router\Router::getRoutes
	 */
	public function testHasAndGetRoute()
	{
		$this->instance->addRoute($route = new Route('foo', '/foo'));

		$this->assertFalse($this->instance->hasRoute('bar'));
		$this->assertTrue($this->instance->hasRoute('foo'));

		$this->assertNull($this->instance->getRoute('bar'));
		$this->assertSame($route, $this->instance->getRoute('foo'));
	}

	/**
	 * Method to test match().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::match
	 */
	public function testMatch()
	{
		$routes = array(
			new Route(null, 'flower/(id)/(alias)', array('_controller' => 'FlowerController')),
			new Route('sakura', 'foo/bar(/id,sakura)', array('_controller' => 'SakuraController')),
		);

		$this->instance->addRoutes($routes);

		$result = $this->instance->match('flower/5/foo');

		$this->assertInstanceOf('Windwalker\Router\Route', $result);

		$result = $result->getVariables();

		$this->assertEquals('FlowerController', $result['_controller']);
		$this->assertEquals('foo', $result['alias']);

		$result = $this->instance->match('foo/bar/5/baz');

		$this->assertInstanceOf('Windwalker\Router\Route', $result);

		$result = $result->getVariables();

		$this->assertEquals('SakuraController', $result['_controller']);
		$this->assertEquals('baz', $result['sakura']);
	}

	/**
	 * Method to test build().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::build
	 */
	public function testBuild()
	{
		$routes = array(
			new Route('flower', 'flower/(id)/(alias)', array('_controller' => 'FlowerController')),
			new Route('sakura', 'foo/bar(/id,sakura)', array('_controller' => 'SakuraController')),
		);

		$this->instance->addRoutes($routes);

		$this->assertEquals('flower/25/sakura', $this->instance->build('flower', array('id' => 25, 'alias' => 'sakura')));
		$this->assertEquals('/flower/25/sakura', $this->instance->build('flower', array('id' => 25, 'alias' => 'sakura'), true));
	}

	/**
	 * Method to test getMethod().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::getMethod
	 */
	public function testGetMethod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setMethod().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::setMethod
	 * @TODO   Implement testSetMethod().
	 */
	public function testSetMethod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getMatcher().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Router::getMatcher
	 */
	public function testGetAndSetMatcher()
	{
		$this->assertInstanceOf('Windwalker\Router\Matcher\MatcherInterface', $this->instance->getMatcher());

		$matcher = new TrieMatcher;

		$this->instance->setMatcher($matcher);

		$this->assertSame($matcher, $this->instance->getMatcher());
	}
}
