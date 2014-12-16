<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form\Filter;

/**
 * The MockFilter class to use on test case.
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
		return 'mock test';
	}
}
