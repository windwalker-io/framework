<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Filesystem\Iterator;

/**
 * A Directory iterator extends from SPL RecursiveDirectoryIterator.
 *
 * @since  2.0
 */
class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
	/**
	 * Get file information of the current element.
	 *
	 * We remove . and .. when fetching folders' path.
	 *
	 * @return  \SplFileInfo  The filename, file information, or $this depending on the set flags.
	 *          See the: http://www.php.net/manual/en/class.filesystemiterator.php#filesystemiterator.constants
	 *
	 * @since  2.0
	 */
	public function current()
	{
		$name = $this->getPathname();

		$endletters = DIRECTORY_SEPARATOR . '.';

		if (substr($name, -2) == $endletters)
		{
			$name = substr($name, 0, -2);
		}

		$file = new \SplFileInfo($name);

		return $file;
	}
}
