<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratoeBundle\Controller;

use Joomla\Input;
use Joomla\Console\Console;
use Joomla\Console\Command\Command;
use Windwalker\Console\Controller\Controller as ConsoleController;

/**
 * CodeGenerator Controller.
 */
abstract class Controller extends ConsoleController
{
	/**
	 * The application object.
	 *
	 * @var    Console
	 */
	protected $app;

	/**
	 * The input object.
	 *
	 * @var    Input\Cli
	 */
	protected $input;

	/**
	 * Property command.
	 *
	 * @var  Command
	 */
	protected $command;

	/**
	 * Instantiate the controller.
	 *
	 * @param   Command  $command  The command object.
	 */
	public function __construct(Command $command)
	{
		// Setup dependencies.
		$this->app     = $command->getApplication();
		$this->input   = $command->getInput() ? : new Input\Cli;
		$this->command = $command;
	}

	/**
	 * Get the application object.
	 *
	 * @return  Console  The application object.
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return  Input\Cli  The input object.
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Load the input object.
	 *
	 * @return  Input\Cli  The input object.
	 */
	protected function loadInput()
	{
		return $this->app->input;
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  Command  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function out($text = '', $nl = true)
	{
		$this->command->out($text, $nl);

		return $this;
	}

	/**
	 * Write a string to standard error output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  Command  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function err($text = '', $nl = true)
	{
		$this->command->err($text, $nl);

		return $this;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @param   string  $question  The question you want to ask user.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   1.0
	 */
	public function in($question = '')
	{
		return $this->command->in($question);
	}

	/**
	 * close
	 *
	 * @param string $text
	 * @param bool   $nl
	 *
	 * @return  void
	 */
	public function close($text = '', $nl = false)
	{
		$this->out($text, $nl);

		die;
	}
}
