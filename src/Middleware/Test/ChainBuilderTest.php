<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware\Test;

use Windwalker\Middleware\Chain\ChainBuilder;
use Windwalker\Middleware\Test\Stub\StubCaesarMiddleware;
use Windwalker\Middleware\Test\Stub\StubOthelloMiddleware;
use Windwalker\Test\TestCase\AbstractBaseTestCase;
use Windwalker\Test\TestHelper;

/**
 * Test class of ChainBuilder
 *
 * @since {DEPLOY_VERSION}
 */
class ChainBuilderTest extends AbstractBaseTestCase
{
	/**
	 * Test instance.
	 *
	 * @var ChainBuilder
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
		$this->instance = new ChainBuilder;

		$this->instance->add(new StubCaesarMiddleware)
			->add(new StubOthelloMiddleware);
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
	 * Method to test add().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Middleware\Chain\ChainBuilder::add
	 */
	public function testAdd()
	{
		$builder = new ChainBuilder;

		$builder->add(new StubCaesarMiddleware)
			->add(new StubOthelloMiddleware);

		$wares = TestHelper::getValue($builder, 'stack');

		$this->assertTrue($wares[0] instanceof StubCaesarMiddleware);
		$this->assertTrue($wares[1] instanceof StubOthelloMiddleware);
	}

	/**
	 * Method to test call().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Middleware\Chain\ChainBuilder::call
	 */
	public function testCall()
	{
		$data = ">>> Caesar
>>> Othello
<<< Othello
<<< Caesar";

		$this->assertStringDataEquals($data, $this->instance->call());
	}
}
