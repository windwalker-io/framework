<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Application;

use Windwalker\IO\Cli\IO;
use Windwalker\IO\Cli\IOInterface;
use Windwalker\Registry\Registry;

/**
 * Simple class for a Windwalker command line application.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractCliApplication extends AbstractApplication
{
	/**
	 * Property io.
	 *
	 * @var  IOInterface
	 */
	public $io = null;

	/**
	 * Class constructor.
	 *
	 * @param   IOInterface $io      An optional argument to provide dependency injection for the application's
	 *                               IO object.
	 * @param   Registry    $config  An optional argument to provide dependency injection for the application's
	 *                               config object.  If the argument is a Registry object that object will become
	 *                               the application's config object, otherwise a default config object is created.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct(IOInterface $io = null, Registry $config = null)
	{
		// Close the application if we are not executed from the command line.
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		$this->io     = $io instanceof IOInterface  ? $io     : new IO;
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
	 * @return  AbstractCliApplication  Instance of $this to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @return  AbstractCliApplication  Return self to support chaining.
	 */
	public function setIO($io)
	{
		$this->io = $io;

		return $this;
	}
}
