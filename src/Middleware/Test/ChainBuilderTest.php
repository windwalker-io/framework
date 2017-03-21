<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
 * @since 2.0
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
	 * @covers \Windwalker\Middleware\Chain\ChainBuilder::add
	 */
	public function testAdd()
	{
		$builder = new ChainBuilder;

		$builder->add(new StubCaesarMiddleware)
			->add(new StubOthelloMiddleware);

		// The ordering will be reverse
		$wares = array_values(iterator_to_array(TestHelper::getValue($builder, 'stack')));

		$this->assertTrue($wares[0] instanceof StubOthelloMiddleware);
		$this->assertTrue($wares[1] instanceof StubCaesarMiddleware);
	}

	/**
	 * Method to test call().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Middleware\Chain\ChainBuilder::execute
	 */
	public function testCall()
	{
		$data = "
>>> Othello
>>> Caesar
<<< Caesar
<<< Othello";

		$this->assertStringSafeEquals($data, $this->instance->execute());
	}

	public function testExecuteByArray()
	{
		$middlewares = [
			new StubOthelloMiddleware,
			new StubCaesarMiddleware
		];

		$builder = new ChainBuilder($middlewares);

		$data = "
>>> Othello
>>> Caesar
<<< Caesar
<<< Othello";

		$this->assertStringSafeEquals($data, $builder->execute());

		$middlewares = [
			new StubOthelloMiddleware,
			new StubCaesarMiddleware
		];

		$builder = new ChainBuilder($middlewares, ChainBuilder::SORT_ASC);

		$data = "
>>> Caesar
>>> Othello
<<< Othello
<<< Caesar";

		$this->assertStringSafeEquals($data, $builder->execute());
	}
}
