<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
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
	 * Initialise command.
	 *
	 * @return void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function initialise()
	{
		$this->description('Foo command desc')
			->usage('foo <command> [option]')
			->help('Foo Command Help')
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
