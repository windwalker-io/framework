<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form\Test\Mock;

use Windwalker\Form\Filter\FilterInterface;

/**
 * The MockFilter class.
 * 
 * @since  2.0
 */
class MockFilter implements FilterInterface
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
		return 'abc';
	}
}
