<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Tests\Stubs;

use Windwalker\Console\Command\Command;
use Windwalker\Console\Tests\Stubs\Foo\AaaCommand;

/**
 * Class FooCommand
 *
 * @since  1.0
 */
class FooCommand extends Command
{
	/**
	 * Command name.
	 *
	 * @var string
	 */
	protected $name = 'foo';

	/**
	 * Configure command.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	protected function configure()
	{
		$this->setDescription('Foo command desc')
			->setUsage('foo <command> [option]')
			->setHelp('Foo Command Help')
			->addCommand(new AaaCommand);
	}

	/**
	 * doExecute
	 *
	 * @return int
	 *
	 * @since  1.0
	 */
	public function doExecute()
	{
		return 123;
	}
}
