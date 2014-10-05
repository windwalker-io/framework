<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Test\Stubs\Foo\Aaa;

use Windwalker\Console\Command\Command;

/**
 * Class BbbCommand
 *
 * @since  {DEPLOY_VERSION}
 */
class BbbCommand extends Command
{
	/**
	 * Configure command.
	 *
	 * @return void
	 *
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	public function doExecute()
	{
		$this->out('Bbb Command', false);

		return 99;
	}
}
