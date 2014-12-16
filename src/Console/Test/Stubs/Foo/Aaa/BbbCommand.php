<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Test\Stubs\Foo\Aaa;

use Windwalker\Console\Command\Command;

/**
 * Class BbbCommand
 *
 * @since  2.0
 */
class BbbCommand extends Command
{
	/**
	 * Initialise command.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	public function initialise()
	{
		$this->setName('bbb');
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
		$this->out('Bbb Command', false);

		return 99;
	}
}
