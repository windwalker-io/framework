<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Filter;

/**
 * Interface FilterInterface
 *
 * @since  {DEPLOY_VERSION}
 */
interface FilterInterface
{
	/**
	 * clean
	 *
	 * @param string $text
	 *
	 * @return  mixed
	 */
	public function clean($text);
}

