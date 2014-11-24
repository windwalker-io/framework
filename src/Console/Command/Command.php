<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Command;

/**
 * Base Command class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Command extends AbstractCommand
{
	/**
	 * Execute this command.
	 *
	 * @return int
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function doExecute()
	{
		$this->io->setArguments(array($this->name));

		$output = $this->app->describeCommand($this);

		$this->out($output);

		return;
	}

	/**
	 * Add an argument(sub command) setting. This method in Command use 'self' instead 'static' to make sure every sub
	 * command add Command class as arguments.
	 *
	 * @param   string|AbstractCommand  $command      The argument name or Console object.
	 *                                                If we just send a string, the object will auto create.
	 * @param   null                    $description  Console description.
	 * @param   array                   $options      Console options.
	 * @param   \Closure                $code         The closure to execute.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function addCommand($command, $description = null, $options = array(), \Closure $code = null)
	{
		if (!($command instanceof AbstractCommand))
		{
			$command = new self($command, $this->io, $this);
		}

		return parent::addCommand($command, $description, $options, $code);
	}
}
