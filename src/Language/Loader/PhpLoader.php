<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Language\Loader;

/**
 * Class PhpLoader
 *
 * @since 1.0
 */
class PhpLoader extends FileLoader
{
	/**
	 * load
	 *
	 * @param string $file
	 *
	 * @throws \RuntimeException
	 * @return  null|string
	 */
	public function load($file)
	{
		if (!is_file($file))
		{
			if (!$file = $this->findFile($file))
			{
				throw new \RuntimeException(sprintf('Language file: %s not found.', $file));
			}
		}

		return include $file;
	}
}

