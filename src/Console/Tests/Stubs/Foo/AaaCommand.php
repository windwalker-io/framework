<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Tests\Stubs\Foo;

use Windwalker\Console\Command\Command;

/**
 * Class AaaCommand
 *
 * @since  1.0
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
	 * Configure command.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function configure()
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
	 * @since  1.0
	 */
	public function doExecute()
	{
		echo 'Aaa';
	}
}
