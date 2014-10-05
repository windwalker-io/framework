<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Prompter;

/**
 * General text prompter.
 *
 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function ask($msg = '', $default = null)
	{
		$default = $default ? : $this->default;

		return $this->in($msg) ? : $default;
	}
}

