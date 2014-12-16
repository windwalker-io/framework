<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Test\Stubs\Foo;

use Windwalker\Console\Command\Command;

/**
 * Class AaaCommand
 *
 * @since  2.0
 */
class AaaCommand extends Command
{
	/**
	 * Command name.
	 *
	 * @var string
	 */
	protected $name = 'aaa';

	/**
	 * Initialise command.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function initialise()
	{
		$this->addCommand(new Aaa\BbbCommand)
			->addOption(
				array('a', 'aaa', 'a3'),
				true,
				'AAA options',
				true
			);
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
		echo 'Aaa';
	}
}
