<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Test;

use Windwalker\Controller\Test\Stub\StubMultiActionController;

/**
 * Test class of AbstractMultiActionController
 *
 * @since {DEPLOY_VERSION}
 */
class AbstractMultiActionControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubMultiActionController
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
		$this->instance = new StubMultiActionController;
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
	 * Method to test execute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Controller\AbstractMultiActionController::execute
	 */
	public function testExecute()
	{
		$this->assertEquals('index', $this->instance->execute());

		$this->instance->setAction('update')->setArguments(array(5, 'Flower'));

		$this->assertEquals('ID: 5 Title: Flower', $this->instance->execute());

		$this->instance->setAction('create');

		try
		{
			$this->instance->execute();
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('LogicException', $e);
		}
	}

	/**
	 * Method to test getArguments().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Controller\AbstractMultiActionController::getArguments
	 */
	public function testGetAndSetArguments()
	{
		$this->instance->setArguments(array(5, 'Flower'));

		$this->assertEquals(array(5, 'Flower'), $this->instance->getArguments());
	}

	/**
	 * Method to test getAction().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Controller\AbstractMultiActionController::getAction
	 * @covers Windwalker\Controller\AbstractMultiActionController::setAction
	 */
	public function testGetAndSetAction()
	{
		$this->instance->setAction('update')->setArguments(array(5, 'Flower'));

		$this->assertEquals('ID: 5 Title: Flower', $this->instance->execute());

		$this->assertEquals('update', $this->instance->getAction());
	}
}
