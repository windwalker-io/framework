<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Exception;

use Windwalker\Console\Command\AbstractCommand;

/**
 * Command not found exception.
 *
 * @since  {DEPLOY_VERSION}
 */
class CommandNotFoundException extends \RuntimeException
{
	/**
	 * Current command to provide information for debug.
	 *
	 * @var AbstractCommand
	 *
	 * @since  {DEPLOY_VERSION}
	 *
	 */
	protected $command;

	/**
	 * The last argument to auto complete.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $argument;

	/**
	 * Exception constructor.
	 *
	 * @param   string           $message   The Exception message to throw.
	 * @param   AbstractCommand  $command   Current command to provide information for debug.
	 * @param   string           $argument  The last argument to auto complete.
	 *
	 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	public function getCommand()
	{
		return $this->command;
	}
}
