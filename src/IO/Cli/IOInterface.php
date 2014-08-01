<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Cli;

/**
 * Class IOInterface
 *
 * @since 1.0
 */
interface IOInterface
{
	/**
	 * Write a string to standard output
	 *
	 * @param   string  $text  The text to display.
	 *
	 * @return  IOInterface  Instance of $this to allow chaining.
	 */
	public function out($text = '');

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 */
	public function in();

	/**
	 * Write a string to standard error output.
	 *
	 * @param   string   $text  The text to display.
	 *
	 * @return  IOInterface
	 */
	public function err($text = '');

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   1.0
	 */
	public function getOption($name, $default = null);

	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setOption($name, $value);

	/**
	 * getOptions
	 *
	 * @return  string[]
	 */
	public function getOptions();

	/**
	 * getArgument
	 *
	 * @param integer $offset
	 * @param mixed   $default
	 *
	 * @return  mixed
	 */
	public function getArgument($offset, $default = null);

	/**
	 * setArgument
	 *
	 * @param integer $offset
	 * @param mixed   $value
	 *
	 * @return  IOInterface
	 */
	public function setArgument($offset, $value);

	/**
	 * getArguments
	 *
	 * @return  string[]
	 */
	public function getArguments();

	/**
	 * shiftArgument
	 *
	 * @return  string
	 */
	public function shiftArgument();

	/**
	 * unshiftArgument
	 *
	 * @param string $arg
	 *
	 * @return  IOInterface
	 */
	public function unshiftArgument($arg);

	/**
	 * popArgument
	 *
	 * @return  string
	 */
	public function popArgument();

	/**
	 * pushArgument
	 *
	 * @param string $arg
	 *
	 * @return  IOInterface
	 */
	public function pushArgument($arg);

	/**
	 * set Arguments
	 *
	 * @param array $args
	 *
	 * @return  IO
	 */
	public function setArguments(array $args);

	/**
	 * getExecuted
	 *
	 * @return  mixed
	 */
	public function getCalledScript();

	/**
	 * addColor
	 *
	 * @param   string $name    The color name.
	 * @param   string $fg      Foreground color.
	 * @param   string $bg      Background color.
	 * @param   array  $options Style options.
	 *
	 * @return  static
	 */
	public function addColor($name, $fg, $bg, $options = array());

	/**
	 * useColor
	 *
	 * @param boolean $bool
	 *
	 * @return  IOInterface
	 */
	public function useColor($bool = true);

	/**
	 * __clone
	 *
	 * @return  void
	 */
	public function __clone();
}

