<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Language\Loader;

/**
 * Class PhpLoader
 *
 * @since 2.0
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

