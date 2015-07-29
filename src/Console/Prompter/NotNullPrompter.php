<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Prompter;

/**
 * The NotNullPrompter class.
 * 
 * @since  2.0
 */
class NotNullPrompter extends ValidatePrompter
{
	/**
	 * Retry times.
	 *
	 * @var  int
	 *
	 * @since  2.0
	 */
	protected $attempt = 3;

	/**
	 * If this property set to true, application will be closed when validate fail.
	 *
	 * @var  boolean
	 *
	 * @since  2.0
	 */
	protected $failToClose = false;

	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $noValidMessage = '  No value?';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $closeMessage = '  Please enter something.';

	/**
	 * Get callable handler.
	 *
	 * @return  callable  The validate callback.
	 *
	 * @since   2.0
	 */
	public function getHandler()
	{
		return function($value)
		{
			return (!is_null($value) && $value !== '');
		};
	}
}
