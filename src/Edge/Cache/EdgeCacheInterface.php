<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Cache;

/**
 * Interface EdgeCacheInterface
 *
 * @since  3.0-beta
 */
interface EdgeCacheInterface
{
	/**
	 * isExpired
	 *
	 * @param   string  $path
	 *
	 * @return  boolean
	 */
	public function isExpired($path);

	/**
	 * getCacheKey
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	public function getCacheKey($path);

	/**
	 * get
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	public function load($path);

	/**
	 * store
	 *
	 * @param   string  $path
	 * @param   string  $value
	 *
	 * @return  static
	 */
	public function store($path, $value);

	/**
	 * remove
	 *
	 * @param   string  $path
	 *
	 * @return  static
	 */
	public function remove($path);
}
