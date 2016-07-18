<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Crypt;

/**
 * Interface CryptInterface
 *
 * @since  3.0
 */
interface CryptInterface
{
	/**
	 * encrypt
	 *
	 * @param string $string
	 * @param string $key
	 * @param string $iv
	 *
	 * @return  string
	 */
	public function encrypt($string, $key = null, $iv = null);

	/**
	 * decrypt
	 *
	 * @param string $string
	 * @param string $key
	 * @param string $iv
	 *
	 * @return  string
	 */
	public function decrypt($string, $key = null, $iv = null);

	/**
	 * match
	 *
	 * @param string $string
	 * @param string $hash
	 * @param string $key
	 * @param string $iv
	 *
	 * @return  boolean
	 */
	public function verify($string, $hash, $key = null, $iv = null);
}
