<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Test\Prompter\Stubs;

use Windwalker\Console\Prompter\PasswordPrompter;

/**
 * Class Fake Password Prompter
 *
 * @since 1.0
 */
class FakePasswordPrompter extends PasswordPrompter
{
	/**
	 * We dont't test bash because it break test process in IDE.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
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
	 * @since   1.0
	 */
	protected function findShell()
	{
		return false;
	}
}

