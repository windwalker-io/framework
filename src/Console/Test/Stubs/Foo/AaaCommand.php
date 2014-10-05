<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Test\Stubs\Foo;

use Windwalker\Console\Command\Command;

/**
 * Class AaaCommand
 *
 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	public function doExecute()
	{
		echo 'Aaa';
	}
}
