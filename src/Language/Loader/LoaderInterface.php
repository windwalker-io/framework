<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Language\Loader;

/**
 * Interface LoaderInterface
 */
interface LoaderInterface
{
	/**
	 * getName
	 *
	 * @return  string
	 */
	public function getName();

	/**
	 * load
	 *
	 * @param string $file
	 *
	 * @return  string
	 */
	public function load($file);
}

