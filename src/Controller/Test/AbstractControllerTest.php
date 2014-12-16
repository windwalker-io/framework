<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Controller\Test;

use Windwalker\Controller\Test\Mock\MockApplication;
use Windwalker\Controller\Test\Stub\StubController;
use Windwalker\IO\Input;

/**
 * Test class of AbstractController
 *
 * @since 2.0
 */
class AbstractControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubController
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
		$this->instance = new StubController(new Input, new MockApplication);
	}

	/**
	 * test__constructor
	 *
	 * @return  void
	 */
	public function test__constructor()
	{
		$this->assertInstanceOf(
			'Windwalker\\IO\\Input',
			$this->instance->getInput()
		);

		$this->assertInstanceOf(
			'Windwalker\\Controller\\Test\\Mock\\MockApplication',
			$this->instance->getApplication()
		);
	}

	/**
	 * Method to test getInput().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Controller\AbstractController::getInput
	 */
	public function testGetAndSetInput()
	{
		$input = new Input;

		$this->instance->setInput($input);

		$this->assertSame($input, $this->instance->getInput());
	}

	/**
	 * Method to test getApplication().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Controller\AbstractController::getApplication
	 */
	public function testGetAndSetApplication()
	{
		$app = new MockApplication;

		$this->instance->setApplication($app);

		$this->assertSame($app, $this->instance->getApplication());
	}

	/**
	 * Method to test serialize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Controller\AbstractController::serialize
	 */
	public function testSerialize()
	{
		$this->instance->getInput()->set('foo', 'bar');

		$controller = unserialize(serialize($this->instance));

		$this->assertEquals('bar', $controller->getInput()->get('foo'));

		// App should be drop after serialized.
		$this->assertEquals(null, $controller->getApplication());
	}
}
