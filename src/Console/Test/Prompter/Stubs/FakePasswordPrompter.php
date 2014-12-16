<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Test\Prompter\Stubs;

use Windwalker\Console\Prompter\PasswordPrompter;

/**
 * Class Fake Password Prompter
 *
 * @since 2.0
 */
class FakePasswordPrompter extends PasswordPrompter
{
	/**
	 * We dont't test bash because it break test process in IDE.
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	protected function findStty()
	{
		return true;
	}

	/**
	 * We dont't test bash because it break test process in IDE.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	protected function findShell()
	{
		return false;
	}
}

