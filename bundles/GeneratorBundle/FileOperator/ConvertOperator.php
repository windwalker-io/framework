<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\FileOperator;

use Joomla\Filesystem\File;

/**
 * Class ConvertOperator
 *
 * @since 1.0
 */
class ConvertOperator extends CopyOperator
{
	/**
	 * copyFile
	 *
	 * @param string $src
	 * @param string $dest
	 * @param array  $replace
	 *
	 * @return  void
	 */
	protected function copyFile($src, $dest, $replace = array())
	{
		// Replace dest file name.
		$dest = strtr($dest, $replace);

		if (is_file($dest))
		{
			$this->io->out('File exists: ' . $dest);
		}
		else
		{
			$content = strtr(file_get_contents($src), $replace);

			if (File::write($dest, $content))
			{
				$this->io->out('File created: ' . $dest);
			}
		}
	}
}
