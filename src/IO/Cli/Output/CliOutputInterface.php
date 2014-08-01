<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Cli\Output;

/**
 * Class CliOutputInterface
 *
 * @since 1.0
 */
interface CliOutputInterface
{
	/**
	 * Write a string to standard output
	 *
	 * @param   string  $text  The text to display.
	 *
	 * @return  CliOutputInterface  Instance of $this to allow chaining.
	 */
	public function out($text = '');
}

