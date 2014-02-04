<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Prompter;

use Joomla\Console\Prompter\ValidatePrompter;

/**
 * Class NotNullPrompter
 *
 * @since 1.0
 */
class NotNullPrompter extends ValidatePrompter
{
	/**
	 * Retry times.
	 *
	 * @var  int
	 *
	 * @since  1.0
	 */
	protected $attempt = 3;

	/**
	 * If this property set to true, application will be closed when validate fail.
	 *
	 * @var  boolean
	 *
	 * @since  1.0
	 */
	protected $failToClose = true;

	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $noValidMessage = '  No value?';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $closeMessage = '  Please enter something.';

	/**
	 * Get callable handler.
	 *
	 * @return  callable  The validate callback.
	 *
	 * @since   1.0
	 */
	public function getHandler()
	{
		return function($value)
		{
			return (!is_null($value) && $value !== '');
		};
	}
}
