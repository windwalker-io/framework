<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Language;

/**
 * Interface LanguageInterface
 */
interface LanguageInterface
{
	/**
	 * translate
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function translate($key);

	/**
	 * plural
	 *
	 * @param string $string
	 * @param int    $count
	 *
	 * @return  string
	 */
	public function plural($string, $count = 1);

	/**
	 * sprintf
	 *
	 * @param string $key
	 *
	 * @return  mixed
	 */
	public function sprintf($key);

	/**
	 * exists
	 *
	 * @param string $key
	 *
	 * @return  boolean
	 */
	public function exists($key);
}

