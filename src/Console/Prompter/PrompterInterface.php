<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Prompter;

/**
 * Prompter Interface.
 *
 * Help us show dialog to ask use questions.
 *
 * @since  {DEPLOY_VERSION}
 */
interface PrompterInterface
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
	public function ask($msg = '', $default = '');

	/**
	 * Proxy to ask method.
	 *
	 * @param   string  $default  Default value.
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __invoke($default = '');
}

