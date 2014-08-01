<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Prompter;

use Joomla\Application\Cli\Output\Stdout;
use Joomla\Input;

/**
 * Prompter class.
 *
 * Help us show dialog to ask use questions.
 *
 * @since  1.0
 */
abstract class AbstractPrompter implements PrompterInterface
{
	/**
	 * Property question.
	 *
	 * @var  string
	 */
	protected $question = null;

	/**
	 * The input object.
	 *
	 * @var  Input\Cli
	 *
	 * @since  1.0
	 */
	protected $input = null;

	/**
	 * Output object.
	 *
	 * @var  Stdout
	 *
	 * @since  1.0
	 */
	protected $output = null;

	/**
	 * The default value.
	 *
	 * @var  mixed
	 */
	protected $default = null;

	/**
	 * Input stream, default is STDIN.
	 *
	 * Replace this resource help us easily test this class.
	 *
	 * @var  resource
	 *
	 * @since  1.0
	 */
	protected $inputStream = STDIN;

	/**
	 * Constructor.
	 *
	 * @param   string     $question  The question you want to ask.
	 * @param   $default   $default   The default value.
	 * @param   Input\Cli  $input     The input object.
	 * @param   Stdout     $output    The output object.
	 *
	 * @since   1.0
	 */
	function __construct($question = null, $default = null, Input\Cli $input = null, Stdout $output = null)
	{
		$this->input    = $input  ? : new Input\Cli;
		$this->output   = $output ? : new Stdout;
		$this->question = $question;
		$this->default  = $default;
	}

	/**
	 * Show prompt to ask user.
	 *
	 * @param   string  $msg      Question.
	 * @param   string  $default  Default value.
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   1.0
	 */
	abstract public function ask($msg = '', $default = '');

	/**
	 * Get a value from standard input.
	 *
	 * @param   string  $question  The question you want to ask user.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   1.0
	 */
	public function in($question = null)
	{
		$question = $question ? : $this->question;

		if ($question)
		{
			$this->output->out()->out($question, false);
		}

		$value = rtrim(fread($this->inputStream, 8192), "\n\r");

		$this->output->out();

		return $value;
	}

	/**
	 * Get input object.
	 *
	 * @return  Input\Cli
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Set input object.
	 *
	 * @param   Input\Cli  $input  The input object.
	 *
	 * @return  AbstractPrompter  Return self to support chaining.
	 *
	 * @since   1.0
	 */
	public function setInput($input)
	{
		$this->input = $input;

		return $this;
	}

	/**
	 * Get output object.
	 *
	 * @return  Stdout
	 *
	 * @since   1.0
	 */
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * Set output object.
	 *
	 * @param   Stdout  $output  The output object.
	 *
	 * @return  AbstractPrompter  Return self to support chaining.
	 *
	 * @since   1.0
	 */
	public function setOutput($output)
	{
		$this->output = $output;

		return $this;
	}

	/**
	 * Get input stream resource.
	 *
	 * @return  resource  The input stream resource.
	 *
	 * @since   1.0
	 */
	public function getInputStream()
	{
		return $this->inputStream;
	}

	/**
	 * Set input stream resource, default is STDIN.
	 *
	 * Replace this resource help us easily test this class.
	 *
	 * @param   resource  $inputStream  The input stream resource.
	 *
	 * @return  AbstractPrompter  Return self to support chaining.
	 *
	 * @since   1.0
	 */
	public function setInputStream($inputStream)
	{
		$this->inputStream = $inputStream;

		return $this;
	}

	/**
	 * Proxy to ask method.
	 *
	 * @param   string  $msg      Question.
	 * @param   string  $default  Default value.
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   1.0
	 */
	public function __invoke($msg = null, $default = null)
	{
		return $this->ask($msg, $default);
	}
}
