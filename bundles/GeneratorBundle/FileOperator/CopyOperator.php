<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\FileOperator;

use Joomla\Filesystem\File;
use Windwalker\String\String;

/**
 * Class CopyOperator
 *
 * @since 1.0
 */
class CopyOperator extends AbstractFileOperator
{
	/**
	 * Property replace.
	 *
	 * @var array
	 */
	protected $replace;

	/**
	 * copy
	 *
	 * @param string $src
	 * @param string $dest
	 * @param array  $replace
	 *
	 * @return  void
	 */
	public function copy($src, $dest, $replace = array())
	{
		$replace = $replace ? : $this->replace;

		if (is_file($src))
		{
			$this->copyFile($src, $dest, $replace);
		}
		else
		{
			$this->copyDir($src, $dest, $replace);
		}
	}

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
		$dest = String::parseVariable($dest, $replace);

		if (is_file($dest))
		{
			$this->app->out('File exists: ' . $dest);
		}
		else
		{
			$content = String::parseVariable(file_get_contents($src), $replace);

			if (File::write($dest, $content))
			{
				$this->app->out('File created: ' . $dest);
			}
		}
	}

	/**
	 * copyDir
	 *
	 * @param string $src
	 * @param string $dest
	 * @param array  $replace
	 *
	 * @return  void
	 */
	protected function copyDir($src, $dest, $replace = array())
	{
		$dir = new \RecursiveDirectoryIterator($src);

		$dir = new \RecursiveIteratorIterator($dir);

		foreach ($dir as $file)
		{
			if ($file->isDir())
			{
				continue;
			}

			$srcFile = $file->getRealPath();

			$destFile = str_replace($src, $dest, $srcFile);

			$this->copyFile($srcFile, $destFile, $replace);
		}
	}

	/**
	 * getReplace
	 *
	 * @return  array
	 */
	public function getReplace()
	{
		return $this->replace;
	}

	/**
	 * setReplace
	 *
	 * @param   array $replace
	 *
	 * @return  CopyOperator  Return self to support chaining.
	 */
	public function setReplace($replace)
	{
		$this->replace = $replace;

		return $this;
	}
}
