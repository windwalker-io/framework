<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Output;

use Joomla\Application\Cli\Output\Stdout as JoomlaStdout;

/**
 * Class Stdout.
 *
 * @since  1.0
 */
class Stdout extends JoomlaStdout
{
	/**
	 * Write a string to standard error output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @since   1.0
	 * @return $this
	 */
	public function err($text = '', $nl = true)
	{
		fwrite(STDERR, $this->processor->process($text) . ($nl ? "\n" : null));

		return $this;
	}
}
