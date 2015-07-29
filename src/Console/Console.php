<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
 * @since  2.0
 */
class Console extends AbstractConsole
{
	/**
	 * The Console title.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $name = 'Windwalker Console';

	/**
	 * Version of this application.
	 *
	 * @var string
	 *
	 * @since  2.0
	 */
	protected $version = '1.0';

	/**
	 * Console description.
	 *
	 * @var string
	 *
	 * @since  2.0
	 */
	protected $description = '';

	/**
	 * Property help.
	 *
	 * @var  string
	 */
	protected $help = 'Welcome to Windwalker Console.';

	/**
	 * A default command to run as application.
	 *
	 * @var  AbstractCommand
	 *
	 * @since  2.0
	 */
	protected $rootCommand;

	/**
	 * True to set this app auto exit.
	 *
	 * @var boolean
	 *
	 * @since  2.0
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
	 * @since   2.0
	 */
	public function execute()
	{
		$this->prepareExecute();

		// @event onBeforeExecute

		// Perform application routines.
		$exitCode = $this->doExecute();

		// @event onAfterExecute

		return $this->postExecute($exitCode);
	}

	/**
	 * Method to run the application routines.
	 *
	 * @param   AbstractCommand  $command  The Command object to execute, default will be rootCommand.
	 *
	 * @return  int  The Unix Console/Shell exit code.
	 *
	 * @see     http://tldp.org/LDP/abs/html/exitcodes.html
	 *
	 * @since   2.0
	 * @throws  \LogicException
	 * @throws  \Exception
	 */
	public function doExecute(AbstractCommand $command = null)
	{
		$command = $command ? : $this->getRootCommand();

		if ((!$command->getHandler() && !count($this->io->getArguments())))
		{
			$this->set('show_help', true);
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

			$exitCode = $e->getCode();
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
	 * executeByPath
	 *
	 * @param string $path
	 * @param IO     $io
	 *
	 * @return  int
	 */
	public function executeByPath($path, $io = null)
	{
		$io = $io ? : $this->io;

		$command = $this->getCommand($path);

		if (!$command)
		{
			throw new \UnexpectedValueException('Command: ' . $path . ' not found.');
		}

		$command->setIO($io);
		$command->setApplication($this);

		return $this->doExecute($command);
	}

	/**
	 * Register default command.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function registerRootCommand()
	{
		if ($this->rootCommand)
		{
			return $this;
		}

		$this->rootCommand = new RootCommand(null, $this->io);

		$this->rootCommand->setApplication($this);

		$this->description ? $this->rootCommand->description($this->description) : null;
		$this->help ? $this->rootCommand->help($this->help) : null;

		return $this;
	}

	/**
	 * Register a new Console.
	 *
	 * @param   string  $name  The command name.
	 *
	 * @return  AbstractCommand The created commend.
	 *
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
	 */
	public function setDescription($description)
	{
		$this->getRootCommand()->description($description);

		return $this;
	}

	/**
	 * Set execute code to default command.
	 *
	 * @param   callable  $closure  Console execute code.
	 *
	 * @return  Console  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function setHandler($closure)
	{
		$this->getRootCommand()->handler($closure);

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
		$this->getRootCommand()->usage($usage);

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
		$this->getRootCommand()->help($help);

		return $this;
	}

	/**
	 * Method to get property Help
	 *
	 * @return  string
	 */
	public function getHelp()
	{
		return $this->help;
	}
}
