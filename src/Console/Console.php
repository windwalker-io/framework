<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console;

use Windwalker\Console\Command\AbstractCommand;
use Windwalker\Console\Command\Command;
use Windwalker\Console\Command\RootCommand;
use Windwalker\Console\Command\HelpCommand;
use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;
use Windwalker\Registry\Registry;

/**
 * Class Console
 *
 * @since  {DEPLOY_VERSION}
 */
class Console extends AbstractConsole
{
	/**
	 * The Console title.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $name = 'Windwalker Console';

	/**
	 * Version of this application.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $version = '1.0';

	/**
	 * Console description.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $description = '';

	/**
	 * A default command to run as application.
	 *
	 * @var  AbstractCommand
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $rootCommand;

	/**
	 * True to set this app auto exit.
	 *
	 * @var boolean
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $autoExit;

	/**
	 * Class init.
	 *
	 * @param   IOInterface $io      The Input and output handler.
	 * @param   Registry    $config  Application's config object.
	 */
	public function __construct(IOInterface $io = null, Registry $config = null)
	{
		$io = $io ? : new IO;

		parent::__construct($io, $config);

		$this->registerRootCommand();
	}

	/**
	 * Execute the application.
	 *
	 * @return  int  The Unix Console/Shell exit code.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function execute()
	{
		// @event onBeforeExecute

		// Perform application routines.
		$exitCode = $this->doExecute();

		// @event onAfterExecute

		return $exitCode;
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  int  The Unix Console/Shell exit code.
	 *
	 * @see     http://tldp.org/LDP/abs/html/exitcodes.html
	 *
	 * @since   {DEPLOY_VERSION}
	 * @throws  \LogicException
	 * @throws  \Exception
	 */
	public function doExecute()
	{
		$command  = $this->getRootCommand();

		if ((!$command->getHandler() && !count($this->io->getArguments())))
		{
			$this->io->unshiftArgument('help');
		}

		try
		{
			/*
			 * Exit code is the Linux/Unix command/shell return code to see
			 * whether this script executed is successful or not.
			 *
			 * @see  http://tldp.org/LDP/abs/html/exitcodes.html
			 */
			$exitCode = $command->execute();
		}
		catch (\Exception $e)
		{
			$command->renderException($e);

			$exitCode = $e->getHandler();
		}

		if ($this->autoExit)
		{
			if ($exitCode > 255 || $exitCode == -1)
			{
				$exitCode = 255;
			}

			exit($exitCode);
		}

		return $exitCode;
	}

	/**
	 * Register default command.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function registerRootCommand()
	{
		if ($this->rootCommand)
		{
			return $this;
		}

		$this->rootCommand = new RootCommand(null, $this->io);

		$this->rootCommand->setApplication($this)
			->addCommand(new HelpCommand);

		return $this;
	}

	/**
	 * Register a new Console.
	 *
	 * @param   string  $name  The command name.
	 *
	 * @return  AbstractCommand The created commend.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function register($name)
	{
		return $this->addCommand(new Command($name, $this->io));
	}

	/**
	 * Add a new command object.
	 *
	 * If a command with the same name already exists, it will be overridden.
	 *
	 * @param   AbstractCommand  $command  A Console object.
	 *
	 * @return  AbstractCommand  The registered command.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function addCommand(AbstractCommand $command)
	{
		$this->getRootCommand()->addCommand($command);

		return $command;
	}

	/**
	 * Get command by path.
	 *
	 * Example: getCommand('foo/bar/baz');
	 *
	 * @param   string  $path  The path or name of child.
	 *
	 * @return  AbstractCommand
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getCommand($path)
	{
		return $this->getRootCommand()->getChild($path);
	}

	/**
	 * Sets whether to automatically exit after a command execution or not.
	 *
	 * @param   boolean  $boolean  Whether to automatically exit after a command execution or not.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function setAutoExit($boolean)
	{
		$this->autoExit = (boolean) $boolean;

		return $this;
	}

	/**
	 * Get the default command.
	 *
	 * @return AbstractCommand  Default command.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getRootCommand()
	{
		return $this->rootCommand;
	}

	/**
	 * Get name of this application.
	 *
	 * @return string  Application name.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set name of this application.
	 *
	 * @param   string  $name  Name of this application.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get version.
	 *
	 * @return string Application version.
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Set version.
	 *
	 * @param   string  $version  Set version of this application.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function setVersion($version)
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * Get description.
	 *
	 * @return string  Application description.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getDescription()
	{
		return $this->getRootCommand()->getDescription();
	}

	/**
	 * Set description.
	 *
	 * @param   string  $description  description of this application.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function setDescription($description)
	{
		$this->getRootCommand()->setDescription($description);

		return $this;
	}

	/**
	 * Set execute code to default command.
	 *
	 * @param   callable  $closure  Console execute code.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function setHandler($closure)
	{
		$this->getRootCommand()->setHandler($closure);

		return $this;
	}

	/**
	 * setUsage
	 *
	 * @param string $usage
	 *
	 * @return  $this
	 */
	public function setUsage($usage)
	{
		$this->getRootCommand()->setUsage($usage);

		return $this;
	}

	/**
	 * setHelp
	 *
	 * @param string $help
	 *
	 * @return  $this
	 */
	public function setHelp($help)
	{
		$this->getRootCommand()->setHelp($help);

		return $this;
	}
}
