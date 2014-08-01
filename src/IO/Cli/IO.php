<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Cli;

use Windwalker\IO\Cli\Input\CliInput;
use Windwalker\IO\Cli\Input\CliInputInterface;
use Windwalker\IO\Cli\Output\CliOutput;
use Windwalker\IO\Cli\Output\CliOutputInterface;

/**
 * Class IO
 *
 * @since 1.0
 */
class IO implements IOInterface
{
	/**
	 * Property input.
	 *
	 * @var  CliInputInterface
	 */
	protected $input = null;

	/**
	 * Property output.
	 *
	 * @var  CliOutputInterface
	 */
	protected $output = null;

	/**
	 * Clas init.
	 *
	 * @param CliInputInterface   $input
	 * @param CliOutputInterface  $output
	 */
	public function __construct(CliInputInterface $input = null, CliOutputInterface $output = null)
	{
		$this->input  = $input ? : new CliInput;
		$this->output = $output ? : new CliOutput;
	}

	/**
	 * Write a string to standard output
	 *
	 * @param   string $text The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  IO  Instance of $this to allow chaining.
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
	 */
	public function in()
	{
		return $this->input->in();
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string $name    Name of the value to get.
	 * @param   mixed  $default Default value to return if variable does not exist.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   1.0
	 */
	public function getOption($name, $default = null)
	{
		return $this->input->get($name, $default);
	}

	/**
	 * Sets a value
	 *
	 * @param   string $name  Name of the value to set.
	 * @param   mixed  $value Value to assign to the input.
	 *
	 * @return  IO
	 *
	 * @since   1.0
	 */
	public function setOption($name, $value)
	{
		$this->input->set($name, $value);

		return $this;
	}

	/**
	 * getArgument
	 *
	 * @param integer $offset
	 * @param mixed   $default
	 *
	 * @return  mixed
	 */
	public function getArgument($offset, $default = null)
	{
		return $this->input->getArgument($offset, $default);
	}

	/**
	 * setArgument
	 *
	 * @param integer $offset
	 * @param mixed   $value
	 *
	 * @return  IO
	 */
	public function setArgument($offset, $value)
	{
		$this->input->setArgument($offset, $value);

		return $this;
	}

	/**
	 * getInput
	 *
	 * @return  CliInputInterface
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * setInput
	 *
	 * @param   CliInputInterface $input
	 *
	 * @return  IO  Return self to support chaining.
	 */
	public function setInput(CliInputInterface $input)
	{
		$this->input = $input;

		return $this;
	}

	/**
	 * getOutput
	 *
	 * @return  CliOutputInterface
	 */
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * setOutput
	 *
	 * @param   CliOutputInterface $output
	 *
	 * @return  IO  Return self to support chaining.
	 */
	public function setOutput(CliOutputInterface $output)
	{
		$this->output = $output;

		return $this;
	}

	/**
	 * getExecuted
	 *
	 * @return  mixed
	 */
	public function getCalledScript()
	{
		return $this->input->getCalledScript();
	}
}

