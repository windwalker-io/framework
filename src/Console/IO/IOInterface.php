<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\IO;

use Windwalker\IO\Cli\IOInterface as WindwalkerIOInterface;

/**
 * The IOInterface class.
 * 
 * @since  2.0
 */
interface IOInterface extends WindwalkerIOInterface
{
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
	 * @return  static
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
	 * @return  static
	 */
	public function pushArgument($arg);

	/**
	 * set Arguments
	 *
	 * @param array $args
	 *
	 * @return  static
	 */
	public function setArguments(array $args);

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
	 * @return  static
	 */
	public function useColor($bool = true);

	/**
	 * __clone
	 *
	 * @return  void
	 */
	public function __clone();

	/**
	 * setOutStream
	 *
	 * @param   resource $outStream
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOutputStream($outStream);

	/**
	 * Method to set property errorStream
	 *
	 * @param   resource $errorStream
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setErrorStream($errorStream);

	/**
	 * setInputStream
	 *
	 * @param   resource $inputStream
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setInputStream($inputStream);
}
