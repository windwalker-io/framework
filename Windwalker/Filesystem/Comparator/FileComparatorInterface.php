<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Filesystem\Comparator;

/**
 * Interface FileComparatorInterface
 */
interface FileComparatorInterface
{
	/**
	 * compare
	 *
	 * @param \SplFileInfo $current  Current item's value
	 * @param int          $key      Current item's key
	 * @param \Iterator    $iterator Iterator being filtered
	 *
	 * @return  boolean  TRUE to accept the current item, FALSE otherwise.
	 */
	public function compare($current, $key, $iterator);
}
