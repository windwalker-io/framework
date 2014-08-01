<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Cli\Output;

/**
 * Class SimpleCliOutput
 *
 * @since 1.0
 */
class SimpleCliOutput extends AbstractCliOutput
{
	/**
	 * Write a string to standard output
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  SimpleCliOutput  Instance of $this to allow chaining.
	 */
	public function out($text = '', $nl = true)
	{
		fwrite($this->outputStream, $text . ($nl ? "\n" : null));

		return $this;
	}
}

