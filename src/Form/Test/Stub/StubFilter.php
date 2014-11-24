<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form\Test\Stub;

use Windwalker\Form\Filter\FilterInterface;

/**
 * The StubFilter class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class StubFilter implements FilterInterface
{
	/**
	 * clean
	 *
	 * @param string $text
	 *
	 * @return  mixed
	 */
	public function clean($text)
	{
		return filter_var($text, FILTER_SANITIZE_NUMBER_INT);
	}
}
