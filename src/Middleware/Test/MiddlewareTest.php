<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Middleware\Test;

use Windwalker\Middleware\EndMiddleware;
use Windwalker\Middleware\Test\Stub\StubCaesarMiddleware;
use Windwalker\Middleware\Test\Stub\StubOthelloMiddleware;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * Test class of Middleware
 *
 * @since 2.0
 */
class MiddlewareTest extends AbstractBaseTestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubCaesarMiddleware
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
		$this->instance = new StubCaesarMiddleware;

		$this->instance->setNext(new StubOthelloMiddleware);
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
	 * Method to test getNext().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Middleware\Middleware::getNext
	 */
	public function testGetNext()
	{
		$this->assertInstanceOf('Windwalker\Middleware\Test\Stub\StubOthelloMiddleware', $this->instance->getNext());
	}

	/**
	 * Method to test setNext().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Middleware\Middleware::setNext
	 */
	public function testSetNext()
	{
		$othello = $this->instance->getNext();

		$othello->setNext(new EndMiddleware);

		$expected = <<<EOF
>>> Caesar
>>> Othello
<<< Othello
<<< Caesar
EOF;

		$this->assertStringDataEquals($expected, $this->instance->call());
	}
}
