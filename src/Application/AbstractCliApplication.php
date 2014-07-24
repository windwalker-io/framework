<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Application;

use Windwalker\Application\Cli\CliOutput;
use Windwalker\Application\Cli\CliOutputInterface;
use Windwalker\Input\CliInput;
use Windwalker\Registry\Registry;

/**
 * Base class for a Joomla! command line application.
 *
 * @since  1.0
 */
abstract class AbstractCliApplication extends AbstractApplication
{
	/**
	 * @var    CliOutput  Output object
	 * @since  1.0
	 */
	protected $output;

	/**
	 * Property input.
	 *
	 * @var CliInput
	 */
	protected $input;

	/**
	 * Class constructor.
	 *
	 * @param   CliInput            $input   An optional argument to provide dependency injection for the application's
	 *                                       input object.  If the argument is a InputCli object that object will become
	 *                                       the application's input object, otherwise a default input object is created.
	 * @param   Registry            $config  An optional argument to provide dependency injection for the application's
	 *                                       config object.  If the argument is a Registry object that object will become
	 *                                       the application's config object, otherwise a default config object is created.
	 * @param   CliOutputInterface  $output  The output handler.
	 *
	 * @since   1.0
	 */
	public function __construct(CliInput $input = null, Registry $config = null, CliOutputInterface $output = null)
	{
		// Close the application if we are not executed from the command line.
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		$this->output = ($output instanceof CliOutputInterface) ? $output : new CliOutput;

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($input instanceof CliInput ? $input : new CliInput, $config);

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	/**
	 * Get an output object.
	 *
	 * @return  CliOutput
	 *
	 * @since   1.0
	 */
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  AbstractCliApplication  Instance of $this to allow chaining.
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function out($text = '', $nl = true)
	{
		$this->output->out($text, $nl);

		return $this;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function in()
	{
		return $this->input->in();
	}
}
