<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test\Stubs;

use Windwalker\Console\Command\Command;
use Windwalker\Console\Test\Stubs\Foo\AaaCommand;

/**
 * Class FooCommand
 *
 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
	 */
	public function doExecute()
	{
		return 123;
	}
}
