<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test;

use Windwalker\Console\Console;
use Windwalker\Console\Descriptor\Text\TextCommandDescriptor;
use Windwalker\Console\Descriptor\Text\TextDescriptorHelper;
use Windwalker\Console\Descriptor\Text\TextOptionDescriptor;
use Windwalker\Console\Test\Mock\MockIO;
use Windwalker\Console\Test\Output\TestStdout;
use Windwalker\Console\Test\Stubs\FooCommand;

/**
 * Class TextDescriptorHelperTest
 *
 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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

  <info>-q | --quiet     </info>q desc
  <info>-s | --sakura    </info>sakura desc
  <info>-r               </info>rose desc

Commands:

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
			->description('foo desc')
			->usage('foo command option')
			->help('foo help');

		$command->addCommand('bar', 'Bar command desc');
		$command->addCommand('yoo');
		$command->addOption(array('q', 'quiet'), 'default', 'q desc');
		$command->addOption(array('s', 'sakura'), 'default', 'sakura desc');
		$command->addOption(array('r'), 'default', 'rose desc');

		$result = $this->instance->describe($command);

		$this->assertEquals(
			str_replace("\r\n", "\n", trim($compare)),
			str_replace("\r\n", "\n", trim($result))
		);
	}
}
