<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Prompter;

/**
 * General text prompter.
 *
 * @since  1.0
 */
class TextPrompter extends AbstractPrompter
{
	/**
	 * Show prompt to ask user.
	 *
	 * @param   string  $msg      Question.
	 * @param   string  $default  Default value.
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   1.0
	 */
	public function ask($msg = '', $default = null)
	{
		$default = $default ? : $this->default;

		return $this->in($msg) ? : $default;
	}
}
 