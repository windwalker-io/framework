<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console;

use Windwalker\Console\Command\AbstractCommand;
use Windwalker\Console\Descriptor\DescriptorHelperInterface;
use Windwalker\Console\Descriptor\Text\TextCommandDescriptor;
use Windwalker\Console\Descriptor\Text\TextDescriptorHelper;
use Windwalker\Console\Descriptor\Text\TextOptionDescriptor;
use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;
use Windwalker\Registry\Registry;

/**
 * The AbstractConsole class.
 * 
 * @since  2.0
 */
abstract class AbstractConsole
{
	/**
	 * Property io.
	 *
	 * @var  IOInterface
	 */
	public $io = null;

	/**
	 * Property config.
	 *
	 * @var  Registry
	 */
	protected $config = null;

	/**
	 * Property descriptor.
	 *
	 * @var DescriptorHelperInterface
	 */
	protected $descriptor;

	/**
	 * Class constructor.
	 *
	 * @param   IOInterface  $io      An optional argument to provide dependency injection for the application's
	 *                                IO object.
	 * @param   Registry     $config  An optional argument to provide dependency injection for the config object.
	 *
	 * @since   2.0
	 */
	public function __construct(IOInterface $io = null, Registry $config = null)
	{
		// Close the application if we are not executed from the command line.
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		$this->io = $io instanceof IOInterface ? $io : new IO;
		$this->config = $config instanceof Registry ? $config : new Registry;

		$this->initialise();

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  static  Instance of $this to allow chaining.
	 *
	 * @since   2.0
	 */
	public function out($text = '', $nl = true)
	{
		$this->io->out($text, $nl);

		return $this;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   2.0
	 */
	public function in()
	{
		return $this->io->in();
	}

	/**
	 * getIo
	 *
	 * @return  IOInterface
	 */
	public function getIO()
	{
		return $this->io;
	}

	/**
	 * setIo
	 *
	 * @param   IOInterface $io
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setIO($io)
	{
		$this->io = $io;

		return $this;
	}

	/**
	 * Method to close the application.
	 *
	 * @param   integer|string  $message  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function close($message = 0)
	{
		exit($message);
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @param   AbstractCommand  $command  The Command object to execute, default will be rootCommand.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	abstract protected function doExecute(AbstractCommand $command = null);

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function execute()
	{
		$this->prepareExecute();

		// @event onBeforeExecute

		// Perform application routines.
		$this->doExecute();

		// @event onAfterExecute

		$this->postExecute();
	}

	/**
	 * Prepare execute hook.
	 *
	 * @return  void
	 */
	protected function prepareExecute()
	{
	}

	/**
	 * Pose execute hook.
	 *
	 * @param   mixed  $result  Executed return value.
	 *
	 * @return  mixed
	 */
	protected function postExecute($result = null)
	{
		return $result;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   2.0
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the static::__construct method.
	 * This is for developers to inject initialisation code for their application classes.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function initialise()
	{
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   2.0
	 */
	public function set($key, $value = null)
	{
		$this->config->set($key, $value);

		return $this;
	}

	/**
	 * Sets the configuration for the application.
	 *
	 * @param   Registry  $config  A registry object holding the configuration.
	 *
	 * @return  static  Returns itself to support chaining.
	 *
	 * @since   2.0
	 */
	public function setConfiguration(Registry $config)
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * Method to get property Config
	 *
	 * @return  Registry
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Method to set property config
	 *
	 * @param   Registry $config
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setConfig($config)
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * Get or create descriptor.
	 *
	 * @return DescriptorHelperInterface|TextDescriptorHelper
	 *
	 * @since  2.0
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
	 * Method to set property descriptor
	 *
	 * @param   DescriptorHelperInterface $descriptor
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDescriptor(DescriptorHelperInterface $descriptor)
	{
		$this->descriptor = $descriptor;

		return $this;
	}

	/**
	 * describeCommand
	 *
	 * @param AbstractCommand $command
	 *
	 * @return  string
	 */
	public function describeCommand(AbstractCommand $command)
	{
		$descriptor = $this->getDescriptor();

		return $descriptor->describe($command);
	}
}
