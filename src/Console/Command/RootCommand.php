<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Command;

use Windwalker\Console;
use Windwalker\Console\Option\Option;

/**
 * The default command.
 *
 * @since  {DEPLOY_VERSION}
 */
class RootCommand extends Command
{
	/**
	 * Initialise command.
	 *
	 * @return void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function initialise()
	{
		// Get application file name
		if (!$this->name)
		{
			$file = $_SERVER['argv'][0];
			$file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file);
			$file = explode(DIRECTORY_SEPARATOR, $file);
			$file = array_pop($file);
		}
		else
		{
			$file = $this->name;
		}

		$defaultAnsi = defined('PHP_WINDOWS_VERSION_MAJOR') ? 1 : 0;

		$this->setName($file)
			->setDescription('The default application command')
			->addOption(array('h', 'help'),    0, 'Display this help message.',          Option::IS_GLOBAL)
			->addOption(array('q', 'quiet'),   0, 'Do not output any message.',          Option::IS_GLOBAL)
			->addOption(array('v', 'verbose'), 0, 'Increase the verbosity of messages.', Option::IS_GLOBAL)
			->addOption('no-ansi', $defaultAnsi, "Suppress ANSI colors on unsupported terminals.\nUse <option>--no-ansi=false</option> to force using color.",   Option::IS_GLOBAL)
			->setHelp(
			// @TODO: Complete the help.
<<<HELP
Welcome to Windwalker Console.
HELP
			);

		// Add a style <option> & <cmd>
		$this->io->addColor('option', 'cyan', '', array('bold'))
			->addColor('cmd', 'magenta', '', array('bold'));

		Console\IO\IOFactory::$io = $this->io;
	}
}
