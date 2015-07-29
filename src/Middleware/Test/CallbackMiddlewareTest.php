<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Middleware\Test;

use Windwalker\Middleware\CallbackMiddleware;
use Windwalker\Middleware\EndMiddleware;
use Windwalker\Middleware\Test\Stub\StubOthelloMiddleware;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * Test class of CallbackMiddleware
 *
 * @since 2.0
 */
class CallbackMiddlewareTest extends AbstractBaseTestCase
{
	/**
	 * Test instance.
	 *
	 * @var CallbackMiddleware
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
		$this->instance = new CallbackMiddleware;
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
	 * Method to test call().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Middleware\CallbackMiddleware::call
	 */
	public function testCall()
	{
		$this->instance->setHandler(
			function($next)
			{
				$r = "Coriolanus\n";

				$r .= $next->call();

				return $r .= "Coriolanus\n";
			}
		);

		$othello = new StubOthelloMiddleware;

		$othello->setNext(new EndMiddleware);

		$this->instance->setNext($othello);

		$data = <<<EOF
Coriolanus
>>> Othello
<<< Othello
Coriolanus
EOF;

		$this->assertStringSafeEquals($data, $this->instance->call());
	}

	/**
	 * Method to test getHandler().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Middleware\CallbackMiddleware::getHandler
	 * @TODO   Implement testGetHandler().
	 */
	public function testGetHandler()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setHandler().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Middleware\CallbackMiddleware::setHandler
	 * @TODO   Implement testSetHandler().
	 */
	public function testSetHandler()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
