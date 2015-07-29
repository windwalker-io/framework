<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\IO;

use Windwalker\IO\Cli\Color\ColorProcessor;
use Windwalker\IO\Cli\Color\ColorStyle;
use Windwalker\IO\Cli\Color\NoColorProcessor;
use Windwalker\IO\Cli\IO as WindwalkerIO;
use Windwalker\IO\Cli\Output\ColorfulOutputInterface;

if (!interface_exists('JsonSerializable'))
{
	include_once __DIR__ . '/../Compat/JsonSerializable.php';
}

/**
 * The IO class.
 * 
 * @since  2.0
 */
class IO extends WindwalkerIO implements IOInterface, \IteratorAggregate, \ArrayAccess, \Serializable, \Countable, \JsonSerializable
{
	/**
	 * set Arguments
	 *
	 * @param array $args
	 *
	 * @return  IO
	 */
	public function setArguments(array $args)
	{
		$this->input->args = $args;

		return $this;
	}

	/**
	 * shiftArgument
	 *
	 * @return  string
	 */
	public function shiftArgument()
	{
		return array_shift($this->input->args);
	}

	/**
	 * unshiftArgument
	 *
	 * @param string $arg
	 *
	 * @return  IOInterface
	 */
	public function unshiftArgument($arg)
	{
		array_unshift($this->input->args, $arg);

		return $this;
	}

	/**
	 * pushArgument
	 *
	 * @param string $arg
	 *
	 * @return  IOInterface
	 */
	public function pushArgument($arg)
	{
		array_push($this->input->args, $arg);

		return $this;
	}

	/**
	 * popArgument
	 *
	 * @return  string
	 */
	public function popArgument()
	{
		return array_pop($this->input->args);
	}

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
	public function addColor($name, $fg, $bg, $options = array())
	{
		if ($this->output instanceof ColorfulOutputInterface)
		{
			$this->output->getProcessor()->addStyle($name, new ColorStyle($fg, $bg, $options));
		}

		return $this;
	}

	/**
	 * useColor
	 *
	 * @param boolean $bool
	 *
	 * @return  IOInterface
	 */
	public function useColor($bool = true)
	{
		if ($this->output instanceof ColorfulOutputInterface)
		{
			$this->output->getProcessor()->setNoColors(!$bool);
		}

		return $this;
	}

	/**
	 * __clone
	 *
	 * @return  void
	 */
	public function __clone()
	{
		$this->input = clone $this->input;
		$this->output = clone $this->output;
	}

	/**
	 * getOutStream
	 *
	 * @return  resource
	 */
	public function getOutputStream()
	{
		return $this->output->getOutputStream();
	}

	/**
	 * setOutStream
	 *
	 * @param   resource $outStream
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOutputStream($outStream)
	{
		$this->output->setOutputStream($outStream);

		return $this;
	}

	/**
	 * Method to get property ErrorStream
	 *
	 * @return  resource
	 */
	public function getErrorStream()
	{
		return $this->output->getErrorStream();
	}

	/**
	 * Method to set property errorStream
	 *
	 * @param   resource $errorStream
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setErrorStream($errorStream)
	{
		$this->output->setErrorStream($errorStream);

		return $this;
	}

	/**
	 * getInputStream
	 *
	 * @return  resource
	 */
	public function getInputStream()
	{
		return $this->input->getInputStream();
	}

	/**
	 * setInputStream
	 *
	 * @param   resource $inputStream
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setInputStream($inputStream)
	{
		$this->input->setInputStream($inputStream);

		return $this;
	}
}
