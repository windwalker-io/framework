<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Command;

use Windwalker\Console\Descriptor\DescriptorHelperInterface;
use Windwalker\Console\Descriptor\Text\TextDescriptorHelper;
use Windwalker\Console\Descriptor\Text\TextCommandDescriptor;
use Windwalker\Console\Descriptor\Text\TextOptionDescriptor;
use Windwalker\Console\Exception\CommandNotFoundException;

/**
 * Command to list all arguments.
 *
 * @since  {DEPLOY_VERSION}
 */
class HelpCommand extends Command
{
	/**
	 * Command(Argument) name.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $name = 'help';

	/**
	 * The AbstractDescriptor Helper.
	 *
	 * @var  DescriptorHelperInterface
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $descriptor;

	/**
	 * The command we want to described.
	 *
	 * @var  Command
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $describedCommand;

	/**
	 * Configure command.
	 *
	 * @return void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function configure()
	{
		$this->setDescription('List all arguments and show usage & manual.');
	}

	/**
	 * Execute this command.
	 *
	 * @return int The exit code.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function doExecute()
	{
		$args = $this->io->getArguments();

		$command = $this->getDescribedCommand($args);

		if (!$command->getApplication())
		{
			$command->setApplication($this->application);
		}

		$descriptor = $this->getDescriptor();

		/** @var $command Command */
		$rendered = $descriptor->describe($command);

		$this->out($rendered);

		return;
	}

	/**
	 * Get the command we want to describe.
	 *
	 * @param   array  $args  Arguments of this execute.
	 *
	 * @return  AbstractCommand|null
	 *
	 * @since  {DEPLOY_VERSION}
	 *
	 * @throws  CommandNotFoundException
	 */
	protected function getDescribedCommand($args)
	{
		$this->describedCommand = $command = $this->getParent();

		foreach ($args as $arg)
		{
			$command = $command->getChild($arg);

			if (!$command)
			{
				throw new CommandNotFoundException(sprintf('Command: "%s" not found.', implode(' ', $args)), $this->describedCommand, $arg);
			}

			// Set current to describedCommand that we can use it auto complete wrong args.
			$this->describedCommand = $command;
		}

		return $command;
	}

	/**
	 * Get or create descriptor.
	 *
	 * @return DescriptorHelperInterface|TextDescriptorHelper
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getDescriptor()
	{
		if (!$this->descriptor)
		{
			$this->descriptor = new TextDescriptorHelper(
				new TextCommandDescriptor,
				new TextOptionDescriptor
			);
		}

		return $this->descriptor;
	}

	/**
	 * Set descriptor helper.
	 *
	 * @param   DescriptorHelperInterface  $descriptor  Descriptor helper.
	 *
	 * @return  $this
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setDescriptor(DescriptorHelperInterface $descriptor)
	{
		$this->descriptor = $descriptor;

		return $this;
	}
}
