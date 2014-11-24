<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Application\Test;

use Psr\Log\NullLogger;
use Windwalker\Application\Test\Mock\MockLogger;
use Windwalker\Application\Test\Stub\StubApplication;
use Windwalker\Registry\Registry;

/**
 * Test class of AbstractApplication
 *
 * @since {DEPLOY_VERSION}
 */
class AbstractApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubApplication
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
		$this->instance = new StubApplication;
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
	 * Method to test close().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractApplication::close
	 */
	public function testClose()
	{
		$this->assertEquals(0, $this->instance->close());
	}

	/**
	 * Method to test execute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractApplication::execute
	 */
	public function testExecute()
	{
		$this->instance->execute();

		$this->assertEquals('Hello World', $this->instance->executed);
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractApplication::get
	 */
	public function testGetAndSet()
	{
		$config = array(
			'flower' => 'sakura',
			'sky' => array(
				'bird' => 'seagull'
			)
		);

		$this->instance->setConfiguration(new Registry($config));

		$this->assertEquals('sakura', $this->instance->get('flower'));
		$this->assertEquals('seagull', $this->instance->get('sky.bird'));
		$this->assertEquals('foo', $this->instance->get('bar', 'foo'));

		$this->instance->set('packour', 'run');

		$this->assertEquals('run', $this->instance->get('packour', 'foo'));
	}

	/**
	 * Method to test getLogger().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractApplication::getLogger
	 */
	public function testGetAndSetLogger()
	{
		$logger = $this->instance->getLogger();

		$this->assertTrue($logger instanceof NullLogger, 'Default logger should be NullLogger.');

		$this->instance->setLogger(new MockLogger);

		$this->assertTrue($this->instance->getLogger() instanceof MockLogger);
	}

	/**
	 * Method to test setConfiguration().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractApplication::setConfiguration
	 */
	public function testSetConfiguration()
	{
		$config = array(
			'wind' => 'sound'
		);

		$this->instance->setConfiguration(new Registry($config));

		$this->assertEquals('sound', $this->instance->get('wind'));
	}
}
