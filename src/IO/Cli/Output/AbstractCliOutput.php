<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Cli\Output;

/**
 * Class AbstractCliOutput
 *
 * @since 1.0
 */
abstract class AbstractCliOutput implements CliOutputInterface
{
	/**
	 * Property outStream.
	 *
	 * @var  resource
	 */
	protected $outputStream = STDOUT;

	/**
	 * getOutStream
	 *
	 * @return  resource
	 */
	public function getOutputStream()
	{
		return $this->outputStream;
	}

	/**
	 * setOutStream
	 *
	 * @param   resource $outStream
	 *
	 * @return  AbstractCliOutput  Return self to support chaining.
	 */
	public function setOutputStream($outStream)
	{
		$this->outputStream = $outStream;

		return $this;
	}
}
 