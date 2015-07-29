<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Exception;

use Windwalker\Console\Command\AbstractCommand;

/**
 * Command not found exception.
 *
 * @since  2.0
 */
class CommandNotFoundException extends \RuntimeException
{
	/**
	 * Current command to provide information for debug.
	 *
	 * @var AbstractCommand
	 *
	 * @since  2.0
	 *
	 */
	protected $command;

	/**
	 * The last argument to auto complete.
	 *
	 * @var string
	 *
	 * @since  2.0
	 */
	protected $argument;

	/**
	 * Exception constructor.
	 *
	 * @param   string           $message   The Exception message to throw.
	 * @param   AbstractCommand  $command   Current command to provide information for debug.
	 * @param   string           $argument  The last argument to auto complete.
	 *
	 * @since  2.0
	 */
	public function __construct($message, AbstractCommand $command, $argument)
	{
		$this->command  = $command;
		$this->argument = $argument;

		parent::__construct($message, 2);
	}

	/**
	 * Argument setter.
	 *
	 * @param   string  $argument  The last argument to auto complete.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function setArgument($argument)
	{
		$this->argument = $argument;
	}

	/**
	 * Argument getter.
	 *
	 * @return string
	 *
	 * @since  2.0
	 */
	public function getChild()
	{
		return $this->argument;
	}

	/**
	 * Command setter.
	 *
	 * @param   AbstractCommand  $command  Current command to provide information for debug.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function setCommand($command)
	{
		$this->command = $command;
	}

	/**
	 * Command getter.
	 *
	 * @return AbstractCommand  $command  Current command to provide information for debug.
	 *
	 * @since  2.0
	 */
	public function getCommand()
	{
		return $this->command;
	}
}
