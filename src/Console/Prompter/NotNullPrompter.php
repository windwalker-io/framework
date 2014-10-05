<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Prompter;

/**
 * The NotNullPrompter class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class NotNullPrompter extends ValidatePrompter
{
	/**
	 * Retry times.
	 *
	 * @var  int
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $attempt = 3;

	/**
	 * If this property set to true, application will be closed when validate fail.
	 *
	 * @var  boolean
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $failToClose = false;

	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $noValidMessage = '  No value?';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $closeMessage = '  Please enter something.';

	/**
	 * Get callable handler.
	 *
	 * @return  callable  The validate callback.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getHandler()
	{
		return function($value)
		{
			return (!is_null($value) && $value !== '');
		};
	}
}
