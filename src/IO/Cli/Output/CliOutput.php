<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Cli\Output;

use Windwalker\IO\Cli\Color\ColorProcessor;
use Windwalker\IO\Cli\Color\ColorProcessorInterface;

/**
 * Simple Cli Output
 *
 * @since 2.0
 */
class CliOutput extends AbstractCliOutput implements ColorfulOutputInterface
{
	/**
	 * Color processing object
	 *
	 * @var    ColorProcessorInterface
	 * @since  2.0
	 */
	protected $processor;

	/**
	 * Constructor
	 *
	 * @param   ColorProcessorInterface  $processor  The output processor.
	 *
	 * @since   2.0
	 */
	public function __construct(ColorProcessorInterface $processor = null)
	{
		$this->setProcessor(($processor instanceof ColorProcessorInterface) ? $processor : new ColorProcessor);
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
		fwrite($this->outputStream, $this->getProcessor()->process($text) . ($nl ? "\n" : null));

		return $this;
	}

	/**
	 * Write a string to standard error output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @since   2.0
	 * @return $this
	 */
	public function err($text = '', $nl = true)
	{
		fwrite($this->errorStream, $this->processor->process($text) . ($nl ? "\n" : null));

		return $this;
	}

	/**
	 * Set a processor
	 *
	 * @param   ColorProcessorInterface  $processor  The output processor.
	 *
	 * @return  CliOutput  Instance of $this to allow chaining.
	 *
	 * @since   2.0
	 */
	public function setProcessor(ColorProcessorInterface $processor)
	{
		$this->processor = $processor;

		return $this;
	}

	/**
	 * Get a processor
	 *
	 * @return  ColorProcessorInterface
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function getProcessor()
	{
		if ($this->processor)
		{
			return $this->processor;
		}

		throw new \RuntimeException('A ColorProcessorInterface object has not been set.');
	}
}

