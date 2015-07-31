<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test;

use Windwalker\Console\Console;
use Windwalker\Console\Test\Mock\MockIO;
use Windwalker\Console\Test\Mock\MockLogger;
use Windwalker\Console\Test\Stubs\Foo\Aaa\BbbCommand;
use Windwalker\Console\Test\Stubs\Foo\AaaCommand;
use Windwalker\Console\Test\Stubs\FooCommand;
use Windwalker\Test\TestHelper;

/**
 * Class ConsoleTest
 *
 * @since  2.0
 */
class ConsoleTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Console
	 *
	 * @since 2.0
	 */
	public $instance;

	/**
	 * Set up test.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	protected function setUp()
	{
		$io = new MockIO;

		$io->setArguments(array('foo'));

		/** @var $console Console */
		$console = new Console($io);

		$console->setName('Test Console')
			->setVersion('1.2.3')
			->setDescription('Test desc.')
			->setAutoExit(false);

		$this->instance = $console;
	}

	/**
	 * testGetAndSetLogger
	 *
	 * @return  void
	 */
	public function testGetAndSetLogger()
	{
		$this->markTestSkipped('Re adding this test when we support PSR Logger');

		/* Re adding this test when we support PSR Logger

		$this->assertInstanceOf('Psr\\Log\\NullLogger', $this->instance->getLogger());

		$this->instance->setLogger(new MockLogger);

		$this->assertInstanceOf('Windwalker\\Console\\Test\\Mock\\MockLogger', $this->instance->getLogger());
		*/
	}

	/**
	 * testGetAndSetConfig
	 *
	 * @return  void
	 */
	public function testGetAndSetConfig()
	{
		$this->instance->set('park.flower', 'sakura');

		$this->assertEquals($this->instance->get('park.flower'), 'sakura', 'Config data not matched.');
	}

	/**
	 * Nested call the command.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testNestedCall()
	{
		$this->instance->addCommand(new FooCommand);

		$this->instance->io->setArguments(array('foo', 'aaa', 'bbb'));

		$code = $this->instance->execute();

		$output = $this->instance->io->getTestOutput();

		$this->assertEquals(99, $code, 'return code not matched.');

		$this->assertEquals('Bbb Command', $output, 'Output not matched.');
	}

	/**
	 * testExecuteByPath
	 *
	 * @return  void
	 */
	public function testExecuteByPath()
	{
		$this->instance->addCommand(new FooCommand);

		$code = $this->instance->executeByPath('foo/aaa/bbb', $this->instance->io);

		$output = $this->instance->io->getTestOutput();

		$this->assertEquals(99, $code, 'return code not matched.');

		$this->assertEquals('Bbb Command', $output, 'Output not matched.');
	}

	/**
	 * testGetCommand
	 *
	 * @return  void
	 */
	public function testGetCommand()
	{
		$this->instance->addCommand(new FooCommand);

		$command = $this->instance->getCommand('foo/aaa');

		$this->assertTrue($command instanceof AaaCommand);

		$command = $this->instance->getCommand('foo/aaa/bbb');

		$this->assertTrue($command instanceof BbbCommand);
	}

	/**
	 * Test autoexit.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testSetAutoExit()
	{
		$this->instance->setAutoExit(true);

		$this->assertEquals(true, TestHelper::getValue($this->instance, 'autoExit'), 'Auto exit need to be TRUE');

		$this->instance->setAutoExit(false);
	}

	/**
	 * test add command.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testAddCommand()
	{
		$this->instance->addCommand(new FooCommand);

		$this->assertEquals('foo', $this->instance->getRootCommand()->getChild('foo')->getName());
	}

	/**
	 * test construct.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testConstruct()
	{
		$console = new Console(new MockIO);

		$this->assertInstanceOf('Windwalker\\Console\\IO\\IO', $console->io);

		$this->assertInstanceOf('Windwalker\\Registry\\Registry', TestHelper::getValue($console, 'config'));
	}



	/**
	 * Test doExecute.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testDoExecute()
	{
		$this->instance->addCommand(new FooCommand);

		$result = $this->instance->execute();

		// Return exit code.
		$this->assertEquals(123, $result, 'Return value wrong');
	}

	/**
	 * Test register default command.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testRegisterRootCommand()
	{
		$this->assertInstanceOf('Windwalker\\Console\\Command\\RootCommand', $this->instance->getRootCommand(), 'Default Command wrong');
	}

	/**
	 * Test register.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testRegister()
	{
		$this->instance->register('bar');

		$this->assertInstanceOf('Windwalker\\Console\\Command\\Command', $this->instance->getRootCommand()->getChild('bar'), 'Need Command instance');
	}

	/**
	 * Test get name.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testGetName()
	{
		$this->assertEquals('Test Console', $this->instance->getName());
	}

	/**
	 * Test set name.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testSetName()
	{
		$this->instance->setName('Test Console2');

		$this->assertEquals('Test Console2', $this->instance->getName());
	}

	/**
	 * Test get version.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testGetVersion()
	{
		$this->assertEquals('1.2.3', $this->instance->getVersion());
	}

	/**
	 * Test set version.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testSetVersion()
	{
		$this->instance->setVersion('3.2.1');

		$this->assertEquals('3.2.1', $this->instance->getVersion());
	}

	/**
	 * Test get description.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testGetDescription()
	{
		$this->assertEquals('Test desc.', $this->instance->getDescription());
	}

	/**
	 * Test set description.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testSetDescription()
	{
		$this->instance->setDescription('Test desc 2.');

		$this->assertEquals('Test desc 2.', $this->instance->getDescription());
	}

	/**
	 * Test set code.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function testSetHandler()
	{
		$this->instance->setHandler(
			function($command)
			{
				return 221;
			}
		);

		$this->assertInstanceOf('\Closure', $this->instance->getRootCommand()->getHandler(), 'Code need to be a closure.');

		$this->assertEquals(221, $this->instance->getRootCommand()->setIO(new MockIO)->execute());
	}
}
