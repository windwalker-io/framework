<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;
use Windwalker\Registry\Registry;

/**
 * The AbstractConsole class.
 * 
 * @since  {DEPLOY_VERSION}
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
	 * Property logger.
	 *
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * Property config.
	 *
	 * @var  Registry
	 */
	protected $config = null;

	/**
	 * Class constructor.
	 *
	 * @param   IOInterface  $io      An optional argument to provide dependency injection for the application's
	 *                                IO object.
	 * @param   Registry     $config  An optional argument to provide dependency injection for the config object.
	 *
	 * @since   1.0
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
	 * @since   1.0
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
	 * @since   1.0
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
	 * @since   1.0
	 */
	public function close($message = 0)
	{
		exit($message);
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	abstract protected function doExecute();

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// @event onBeforeExecute

		// Perform application routines.
		$this->doExecute();

		// @event onAfterExecute
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   1.0
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Get the logger.
	 *
	 * @return  LoggerInterface
	 *
	 * @since   1.0
	 */
	public function getLogger()
	{
		// If a logger hasn't been set, use NullLogger
		if (! ($this->logger instanceof LoggerInterface))
		{
			$this->logger = new NullLogger;
		}

		return $this->logger;
	}

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the static::__construct method.
	 * This is for developers to inject initialisation code for their application classes.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
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
	 * @since   1.0
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
	 * @since   1.0
	 */
	public function setConfiguration(Registry $config)
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * Set the logger.
	 *
	 * @param   LoggerInterface  $logger  The logger.
	 *
	 * @return  static  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;

		return $this;
	}
}
 