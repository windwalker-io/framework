<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Language\Format;

/**
 * Class FormatInterface
 *
 * @since {DEPLOY_VERSION}
 */
interface FormatInterface
{
	/**
	 * getName
	 *
	 * @return  string
	 */
	public function getName();

	/**
	 * parse
	 *
	 * @param string $string
	 *
	 * @return  array
	 */
	public function parse($string);
}

