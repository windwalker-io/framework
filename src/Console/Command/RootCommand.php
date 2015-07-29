<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Command;

use Windwalker\Console;

/**
 * The default command.
 *
 * @since  2.0
 */
class RootCommand extends Command
{
	/**
	 * Initialise command.
	 *
	 * @return void
	 *
	 * @since  2.0
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

		$this->setName($file)
			->description('The default application command')
			->help(
			// @TODO: Complete the help.
				<<<HELP
Welcome to Windwalker Console.
HELP
			);

		$this->addGlobalOption(array('h', 'help'))
			->defaultValue(0)
			->description('Display this help message.');

		$this->addGlobalOption(array('q', 'quiet'))
			->defaultValue(0)
			->description('Do not output any message.');

		$this->addGlobalOption(array('v', 'verbose'))
			->defaultValue(0)
			->description('Increase the verbosity of messages.');

		$this->addGlobalOption('ansi')
			->defaultValue(defined('PHP_WINDOWS_VERSION_MAJOR') ? false : true)
			->description("Set 'off' to suppress ANSI colors on unsupported terminals.");

		// Add a style <option> & <cmd>
		$this->io->addColor('option', 'cyan', '', array('bold'))
			->addColor('cmd', 'magenta', '', array('bold'));

		Console\IO\IOFactory::$io = $this->io;
	}

	/**
	 * prepareExecute
	 *
	 * @throws  \Exception
	 * @return  void
	 */
	protected function prepareExecute()
	{
		if (!($this->app instanceof Console\AbstractConsole))
		{
			throw new \Exception('RootCommand::$app should have Console Application');
		}

		if (!$this->getOption('ansi') || strtolower($this->getOption('ansi')) == 'off')
		{
			$this->app->set('ansi', false);

			$this->io->useColor(false);
		}

		if ($this->getOption('quiet'))
		{
			$this->app->set('quiet', true);
		}

		if ($this->getOption('verbose'))
		{
			$this->app->set('verbose', true);
		}

		if ($this->getOption('help'))
		{
			$this->app->set('show_help', true);
		}
	}
}
