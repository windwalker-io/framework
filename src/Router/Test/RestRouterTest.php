<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test;

use Windwalker\Router\RestRouter;

/**
 * Test class of RestRouter
 *
 * @since 2.0
 */
class RestRouterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var RestRouter
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
		$this->instance = new RestRouter;
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
	 * Method to test isAllowCustomMethod().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\RestRouter::isAllowCustomMethod
	 */
	public function testIsAllowCustomMethod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setHttpMethodSuffix().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\RestRouter::setHttpMethodSuffix
	 * @TODO   Implement testSetHttpMethodSuffix().
	 */
	public function testSetHttpMethodSuffix()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test allowCustomMethod().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\RestRouter::allowCustomMethod
	 * @TODO   Implement testAllowCustomMethod().
	 */
	public function testAllowCustomMethod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCustomMethod().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\RestRouter::getCustomMethod
	 * @TODO   Implement testGetCustomMethod().
	 */
	public function testGetCustomMethod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setCustomMethod().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\RestRouter::setCustomMethod
	 * @TODO   Implement testSetCustomMethod().
	 */
	public function testSetCustomMethod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test match().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\RestRouter::match
	 */
	public function testMatch()
	{
		$routes = array(
			'flower/(id)/(alias)' => 'Flower\\Controller\\',
			'foo/bar(/id,sakura)' => 'Sakura\\Controller\\'
		);

		$this->instance->addMaps($routes);

		$result = $this->instance->match('flower/5/foo');
		$vars = $this->instance->getVariables();

		$this->assertEquals('Flower\\Controller\\Get', $result);
		$this->assertEquals('foo', $vars['alias']);

		$result = $this->instance->match('foo/bar/5/baz', 'POST');
		$vars = $this->instance->getVariables();

		$this->assertEquals('Sakura\\Controller\\Create', $result);
		$this->assertEquals('baz', $vars['sakura']);

		$this->instance
			->allowCustomMethod(true)
			->setCustomMethod('PUT');

		$result = $this->instance->match('foo/bar/5/baz', 'POST');
		$vars = $this->instance->getVariables();

		$this->assertEquals('Sakura\\Controller\\Update', $result);
		$this->assertEquals('baz', $vars['sakura']);
	}
}
