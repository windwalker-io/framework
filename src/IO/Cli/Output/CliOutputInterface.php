<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
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

	/**
	 * Write a string to standard error output.
	 *
	 * @param   string   $text  The text to display.
	 *
	 * @since   1.0
	 * @return $this
	 */
	public function err($text = '');
}

