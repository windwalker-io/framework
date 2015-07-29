<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Cli\Input;

/**
 * Interface CliInputInterface
 */
interface CliInputInterface
{
	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 */
	public function in();

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string $name    Name of the value to get.
	 * @param   mixed  $default Default value to return if variable does not exist.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   2.0
	 */
	public function get($name, $default = null);

	/**
	 * Sets a value
	 *
	 * @param   string $name  Name of the value to set.
	 * @param   mixed  $value Value to assign to the input.
	 *
	 * @return  CliInputInterface
	 *
	 * @since   2.0
	 */
	public function set($name, $value);

	/**
	 * Gets an array of values from the request.
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   2.0
	 */
	public function all();

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
	 * getCalledScript
	 *
	 * @return  string
	 */
	public function getCalledScript();
}

