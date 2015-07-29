<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test;

use Windwalker\Console\Command\Command;
use Windwalker\Console\Command\RootCommand;
use Windwalker\Console\Console;
use Windwalker\Console\Option\Option;
use Windwalker\Console\Test\Mock\MockIO;
use Windwalker\Console\Test\Stubs\FooCommand;

/**
 * Class CommandTest
 *
 * @since  2.0
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Command
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 */
	protected function setUp()
	{
		$command = new RootCommand('default', new MockIO);

		$command->setApplication(new Console);

		$command->addCommand(
			'yoo',
			'yoo desc'
		);

		$command->handler(
			function($command)
			{
				return 123;
			}
		);

		$this->instance = $command;
	}

	/**
	 * Test the execute.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::execute
	 */
	public function testExecute()
	{
		$this->assertEquals(123, $this->instance->execute(), 'Return code is not match.');
	}

	/**
	 * Test the parent getter.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getParent
	 */
	public function testGetParent()
	{
		$parentCommand = new Command('foo');

		$this->instance->setParent($parentCommand);

		$this->assertEquals('foo', $this->instance->getParent()->getName(), 'Parent command not match');
	}

	/**
	 * Test the parent setter.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::setParent
	 */
	public function testSetParent()
	{
		$this->instance->setParent(null);

		$this->assertEquals(null, $this->instance->getParent(), 'Parent command not match');
	}

	/**
	 * Test the add argument method.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::addCommand
	 */
	public function testaddCommand()
	{
		$this->instance->addCommand(
			'bar',
			'bar desc',
			array(
				new Option('a', 0, 'a desc'),
				new Option('b', 0, 'b desc')
			),
			function($command)
			{
				if ($command->getOption('a'))
				{
					return 56;
				}
				else
				{
					return 65;
				}
			}
		);

		$command = $this->instance->getChild('bar');

		$this->assertEquals(65, $command->execute(), 'Wrong exit code returned.');

		// Test option
		$this->instance->getIO()->setOption('a', 1);

		$this->assertEquals(56, $command->execute(), 'Wrong exit code returned.');

		// Test send an instance
		$this->instance->addCommand(new FooCommand);

		$this->assertInstanceOf(
			'Windwalker\\Console\\Test\\Stubs\\FooCommand',
			$this->instance->getChild('foo'),
			'Argument not FooCommand.'
		);
	}

	/**
	 * Test the argument getter.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getChild
	 */
	public function testgetChild()
	{
		$yoo = $this->instance->getChild('yoo');

		$this->assertEquals('yoo desc', $yoo->getDescription(), 'Yoo command desc not match.');
	}

	/**
	 * Test the getChildren methods.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getChildren
	 */
	public function testGetChildren()
	{
		$args = $this->instance->getChildren();

		$this->assertInternalType('array', $args, 'Return not array');

		$this->assertInstanceOf('Windwalker\\Console\\Command\\AbstractCommand', array_shift($args), 'Array element not Command object');
	}

	/**
	 * Test the Add & Get Option.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::addOption
	 */
	public function testAddAndGetOption()
	{
		$cmd = $this->instance;

		$cmd->addGlobalOption(
			array('y', 'yell', 'Y'),
			false,
			'Make return uppercase'
		);

		$cmd->addGlobalOption('y')
			->alias('yell')
			->alias('Y')
			->defaultValue(false)
			->description('Make return uppercase');

		$cmd->getIO()->setOption('y', 1);

		$this->assertSame(1, (int) $cmd->getOption('y'), 'Option value not matched.');

		$this->assertSame(1, (int) $cmd->getOption('yell'), 'Long option value not matched.');

		$this->assertSame(1, (int) $cmd->getOption('Y'), 'uppercase option value not matched.');

		// Test for global option
		$cmd->addCommand(new FooCommand);

		$this->assertSame(1, (int) $cmd->getChild('foo')->getOption('y'), 'Sub command should have global option');

		// Test for children
		$bbb = $cmd->getChild('foo/aaa/bbb');

		$this->assertInstanceOf(
			'Windwalker\\Console\\Option\\Option',
			$bbb->getOptionSet(true)->offsetGet('y'),
			'Sub command "bbb" should have global option'
		);

		// Test default value
		$cmd->addGlobalOption('n')
			->defaultValue('default');

		$cmd->addGlobalOption('h');

		$this->assertEquals('default', $cmd->getOption('n'));
		$this->assertEquals('default2', $cmd->getOption('h', 'default2'));
	}

	/**
	 * Test the options getter.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getOptions
	 */
	public function testGetOptions()
	{
		$cmd = $this->instance;

		$cmd->addOption(
			array('y', 'yell', 'Y'),
			false,
			'Make return uppercase'
		);

		$array = $this->instance->getOptions();

		$this->assertInternalType('array', $array);

		$this->assertInstanceOf('Windwalker\\Console\\Option\\Option', array_shift($array), 'Array element not Option object');
	}

	/**
	 * Test get arg.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getArgument
	 */
	public function testGetArgument()
	{
		$this->instance->getIO()->setArguments(array('flower', 'sakura'));

		$this->assertEquals('flower', $this->instance->getArgument(0), 'First arg not matched.');

		$this->assertEquals('rose', $this->instance->getArgument(2, 'rose'), 'Default value not matched.');

		$callback = function()
		{
			return 'Morning Glory';
		};

		$this->assertEquals('Morning Glory', $this->instance->getArgument(2, $callback), 'Default value not matched.');
	}

	/**
	 * Test get all options.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getAllOptions
	 */
	public function testSetAndGetAllOptions()
	{
		$cmd = $this->instance;

		$cmd->setOptions(
			new Option(
				array('y', 'yell', 'Y'),
				false,
				'Make return uppercase',
				Option::IS_GLOBAL
			)
		);


		$array = $this->instance->getAllOptions();

		$this->assertInternalType('array', $array);

		$this->assertInstanceOf('Windwalker\\Console\\Option\\Option', array_shift($array), 'Array element not Option object');
	}

	/**
	 * Test the description getter & setter.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getDescription
	 */
	public function testSetAndGetDescription()
	{
		$this->instance->description('Wu la la~~~');

		$this->assertEquals('Wu la la~~~', $this->instance->getDescription(), 'Description not matched');
	}

	/**
	 * Test the name getter & setter.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getName
	 */
	public function testSetAndGetName()
	{
		$this->instance->setName('yoo');

		$this->assertEquals('yoo', $this->instance->getName(), 'Wrong name');
	}

	/**
	 * Test get & set code.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getHandler
	 */
	public function testSetAndgetHandler()
	{
		$code = $this->instance->getHandler();

		$this->assertInstanceOf('\Closure', $code, 'Handler not exists');

		$this->instance->handler(null);

		$this->assertEquals(null, $this->instance->getHandler(), 'Handler should have been cleaned');
	}

	/**
	 * Test get & set code by callable.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getHandler
	 */
	public function testSetAndgetCallableHandler()
	{
		$this->instance->handler(array($this, 'fakeHandler'));

		$code = $this->instance->getHandler();

		$this->assertTrue(is_callable($code), 'Handler not exists');

		$this->assertEquals('Hello', $this->instance->execute(), 'Handler result failure.');

		$this->instance->handler(null);

		$this->assertEquals(null, $this->instance->getHandler(), 'Handler should have been cleaned');
	}

	/**
	 * Test get option alias.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getOptionAlias
	 */
	public function testGetOptionAlias()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test set option alias.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::setOptionAliases
	 */
	public function testSetOptionAlias()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test set & get application.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getApplication
	 */
	public function testSetAndGetApplication()
	{
		$this->instance->setApplication(new Console);

		$this->assertInstanceOf('Windwalker\\Console\\Console', $this->instance->getApplication(), 'Returned not Console object.');
	}

	/**
	 * Test set & get help.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getHelp
	 */
	public function testSetAndGetHelp()
	{
		$this->instance->help('Ha Ha Ha');

		$this->assertEquals('Ha Ha Ha', $this->instance->getHelp(), 'Help text not matched.');
	}

	/**
	 * Test set & get usage.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::getUsage
	 */
	public function testSetAndGetUsage()
	{
		$this->instance->usage('yoo <command> [option]');

		$this->assertEquals('yoo <command> [option]', $this->instance->getUsage(), 'Usage text not matched.');
	}


	/**
	 * Test renderAlternatives.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::renderAlternatives
	 */
	public function testRenderAlternatives()
	{
		$compare = 'Command "yo" not found.

Did you mean one of these?
    yoo';

		$this->instance->getIO()->setArguments(array('yo'));

		$this->instance->getIO()->setOption('ansi', false);

		$this->instance->execute();

		$this->assertEquals(
			str_replace(array("\n", "\r"), '', trim($compare)),
			str_replace(array("\n", "\r"), '', trim($this->instance->getIO()->getTestOutput()))
		);
	}

	/**
	 * Test render exception.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::renderException
	 */
	public function testRenderException()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test the out method.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::out
	 */
	public function testOut()
	{
		$this->instance->getIO()->getOutput()->outpur = '';

		$this->instance->out('gogo', false);

		$this->assertEquals('gogo', $this->instance->getIO()->getTestOutput());
	}

	/**
	 * Test the err method.
	 *
	 * @return void
	 *
	 * @since  2.0
	 *
	 * @covers Windwalker\Console\Command\AbstractCommand::err
	 */
	public function testErr()
	{
		$this->instance->getIO()->outputStream = '';

		$this->instance->err('errrr', false);

		$this->assertEquals('errrr', $this->instance->getIO()->getTestOutput());
	}

	/**
	 * fakeHandler
	 *
	 * @param $command
	 *
	 * @return  string
	 */
	public function fakeHandler($command)
	{
		return 'Hello';
	}

	/**
	 * testError
	 *
	 * @return  void
	 *
	 * @expectedException  \Exception
	 */
	public function testError()
	{
		$this->instance->error('test');
	}
}
