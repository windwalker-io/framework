<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Tests;

use Windwalker\Console\Console;
use Windwalker\Console\Descriptor\Text\TextCommandDescriptor;
use Windwalker\Console\Descriptor\Text\TextDescriptorHelper;
use Windwalker\Console\Descriptor\Text\TextOptionDescriptor;
use Windwalker\Console\Tests\Mock\MockIO;
use Windwalker\Console\Tests\Output\TestStdout;
use Windwalker\Console\Tests\Stubs\FooCommand;

/**
 * Class TextDescriptorHelperTest
 *
 * @since  1.0
 */
class TextDescriptorHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var TextDescriptorHelper
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	protected function setUp()
	{
		$this->instance = new TextDescriptorHelper(
			new TextCommandDescriptor,
			new TextOptionDescriptor
		);
	}

	/**
	 * Test describe method.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function testDescribe()
	{
		$compare = '<comment>Test Console</comment> - version: 1.2.3
------------------------------------------------------------

[<comment>foo</comment> Help]

foo desc

Usage:
  foo command option


Options:

  <info>-q / --quiet</info>
      q desc


Available commands:

  <info>aaa    </info>No description

  <info>bar    </info>Bar command desc

  <info>yoo    </info>No description

foo help';

		$console = new Console(new MockIO);

		$console->setName('Test Console')
			->setVersion('1.2.3')
			->setDescription('test desc');

		$command = new FooCommand;

		$command->setApplication($console)
			->setDescription('foo desc')
			->setUsage('foo command option')
			->setHelp('foo help')
			->addCommand(
				'bar',
				'Bar command desc'
			)
			->addCommand('yoo')
			->addOption(array('q', 'quiet'), 'default', 'q desc');

		$result = $this->instance->describe($command);

		$this->assertEquals(
			str_replace(PHP_EOL, "\n", trim($compare)),
			str_replace(PHP_EOL, "\n", trim($result))
		);
	}
}
