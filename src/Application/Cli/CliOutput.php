<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Application\Cli;

/**
 * Simple Cli Output
 *
 * @since 1.0
 */
class CliOutput
{
	/**
	 * Property outStream.
	 *
	 * @var  resource
	 */
	protected $outputStream = STDOUT;

	/**
	 * Class init.
	 *
	 * @param $outputStream
	 */
	public function __construct($outputStream = STDOUT)
	{
		$this->outputStream = $outputStream;
	}

	/**
	 * Write a string to standard output
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  CliOutput  Instance of $this to allow chaining.
	 */
	public function out($text = '', $nl = true)
	{
		fwrite($this->outputStream, $text . ($nl ? "\n" : null));

		return $this;
	}

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
	 * @return  CliOutput  Return self to support chaining.
	 */
	public function setOutputStream($outStream)
	{
		$this->outputStream = $outStream;

		return $this;
	}
}
 