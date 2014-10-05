<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Test\Stubs;

use Windwalker\Console\Command\Command;
use Windwalker\Console\Test\Stubs\Foo\AaaCommand;

/**
 * Class FooCommand
 *
 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	public function doExecute()
	{
		return 123;
	}
}
