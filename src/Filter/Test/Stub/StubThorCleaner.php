<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Filter\Test\Stub;

use Windwalker\Filter\Cleaner\CleanerInterface;

/**
 * The StubThorCleaner class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubThorCleaner implements CleanerInterface
{
	/**
	 * Method to clean text by rule.
	 *
	 * @param   string $source The source to be clean.
	 *
	 * @return  mixed  The cleaned value.
	 */
	public function clean($source)
	{
		return 'God';
	}
}
