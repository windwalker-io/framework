<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Command;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Console\Command\Command as JoomlaCommand;
use Joomla\Application\Cli\CliOutput;
use Joomla\Input;
use Windwalker\Console\OptionSet\OptionSet;

/**
 * Base JCommand class.
 *
 * @since  1.0
 */
abstract class Command extends JoomlaCommand
{
	/**
	 * Console constructor.
	 *
	 * @param   string           $name    Console name.
	 * @param   Input\Cli        $input   Cli input object.
	 * @param   CliOutput        $output  Cli output object.
	 * @param   AbstractCommand  $parent  Parent Console.
	 *
	 * @throws \LogicException
	 *
	 * @since  1.0
	 */
	public function __construct($name = null, Input\Cli $input = null, CliOutput $output = null, AbstractCommand $parent = null)
	{
		$this->globalOptions = OptionSet::getInstance();

		parent::__construct($name, $input, $output, $parent);

		$ref = new \ReflectionClass($this);

		// Register sub commands
		$dirs = new \DirectoryIterator(dirname($ref->getFileName()));

		foreach ($dirs as $dir)
		{
			if (!$dir->isDir() || $dirs->isDot())
			{
				continue;
			}

			$name = ucfirst($dir->getBasename());

			$class = $ref->getNamespaceName() . '\\' . $name . "\\" . $name . 'Command';

			if (class_exists($class) && $class::$isEnabled)
			{
				$this->addCommand(new $class);
			}
		}
	}

	/**
	 * Configure command.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	protected function configure()
	{
		$context = get_class($this);

		\JFactory::getApplication()->triggerEvent('onConsoleLoadCommand', array($context, $this));
	}

	/**
	 * getOrClose
	 *
	 * @param int    $arg
	 * @param string $msg
	 *
	 * @return  string
	 */
	public function getOrClose($arg, $msg = '')
	{
		if (isset($this->input->args[$arg]))
		{
			return $this->input->args[$arg];
		}

		$this->out()->out($msg)
			->out()->out('Usage:')->out($this->usage);

		$this->application->close();
	}
}
