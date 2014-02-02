<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Controller;

use Joomla\Input;
use Windwalker\Console\Application\Console;
use Windwalker\Console\Command\Command;

/**
 * Joomla Platform Base Controller Class
 *
 * @package     Joomla.Platform
 * @subpackage  Controller
 * @since       12.1
 */
abstract class Controller implements \JController
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
		$this->app     = $command->getApplication() ? : $this->loadApplication();
		$this->input   = $command->getInput() ? : $this->loadInput();
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
	 * Serialize the controller.
	 *
	 * @return  string  The serialized controller.
	 */
	public function serialize()
	{
		return serialize($this->input);
	}

	/**
	 * Unserialize the controller.
	 *
	 * @param   string  $input  The serialized controller.
	 *
	 * @return  Controller  Supports chaining.
	 *
	 * @throws  \UnexpectedValueException if input is not the right class.
	 */
	public function unserialize($input)
	{
		// Setup dependencies.
		$this->app = $this->loadApplication();

		// Unserialize the input.
		$this->input = unserialize($input);

		if (!($this->input instanceof Input\Cli))
		{
			throw new \UnexpectedValueException(sprintf('%s::unserialize would not accept a `%s`.', get_class($this), gettype($this->input)));
		}

		return $this;
	}

	/**
	 * Load the application object.
	 *
	 * @return  Console  The application object.
	 */
	protected function loadApplication()
	{
		return \JFactory::getApplication();
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
		return $this->command->getOrClose($arg, $msg);
	}
}
