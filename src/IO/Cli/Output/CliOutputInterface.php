<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\IO\Cli\Output;

/**
 * Class CliOutputInterface
 *
 * @since {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 * @return $this
	 */
	public function err($text = '');
}

