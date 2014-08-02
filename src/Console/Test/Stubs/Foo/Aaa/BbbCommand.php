<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Test\Stubs\Foo\Aaa;

use Windwalker\Console\Command\Command;

/**
 * Class BbbCommand
 *
 * @since  1.0
 */
class BbbCommand extends Command
{
	/**
	 * Configure command.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function configure()
	{
		$this->setName('bbb');
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
		$this->out('Bbb Command', false);

		return 99;
	}
}
