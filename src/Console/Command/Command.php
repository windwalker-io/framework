<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Command;

use Windwalker\Console\IO\IOFactory;

/**
 * Base Command class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Command extends AbstractCommand
{
	/**
	 * Render exception for debugging.
	 *
	 * @param   \Exception  $exception  The exception we want to render.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function renderException($exception)
	{
		if (!$this->getOption('v', 0))
		{
			$this->out('')->out($exception->getMessage());

			return;
		}

		parent::renderException($exception);
	}

	/**
	 * Execute this command.
	 *
	 * @return  mixed  Executed result or exit code.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function execute()
	{
		if (count($this->io->getArguments()) && $this->io->getArgument(0) != 'help'
			&& $this->getOption('h') && !$this->getParent())
		{
			$this->io->unshiftArgument('help');
		}

		if ($this->getOption('no-ansi') && $this->getOption('no-ansi') != 'false')
		{
			$this->io->useColor(false);
		}

		return parent::execute();
	}

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

		$output = $this->application
			->getRootCommand()
			->getChild('help')
			->getDescriptor()
			->describe($this);

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

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  Command  Instance of $this to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function out($text = '', $nl = true)
	{
		if (!$this->getOption('q', 0))
		{
			parent::out($text, $nl);
		}

		return $this;
	}
}
