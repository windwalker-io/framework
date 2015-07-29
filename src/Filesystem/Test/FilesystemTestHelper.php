<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Path;

/**
 * The FilesystemTestHelper class.
 * 
 * @since  2.0
 */
class FilesystemTestHelper
{
	/**
	 * listFiles
	 *
	 * @param \Iterator|array $files
	 *
	 * @return  void
	 */
	public static function listFiles($files)
	{
		foreach ($files as $file)
		{
			echo $file . "\n";
		}
	}

	/**
	 * cleanPaths
	 *
	 * @param array $paths
	 *
	 * @return  mixed
	 */
	public static function cleanPaths($paths)
	{
		foreach ($paths as $key => $path)
		{
			$paths[$key] = Path::clean($path);
		}

		sort($paths);

		return $paths;
	}

	/**
	 * getFilesRescurive
	 *
	 * @param string $folder
	 *
	 * @return  array
	 */
	public static function getFilesRecursive($folder = 'dest')
	{
		return array(
			__DIR__ . '/' . $folder . '/file2.txt',
			__DIR__ . '/' . $folder . '/folder1/level2/file3',
			__DIR__ . '/' . $folder . '/folder1/path1',
			__DIR__ . '/' . $folder . '/folder2/file2.html'
		);
	}

	/**
	 * getFilesRescurive
	 *
	 * @param string $folder
	 *
	 * @return  array
	 */
	public static function getFoldersRecursive($folder = 'dest')
	{
		return array (
			__DIR__ . '/' . $folder . '/folder1',
			__DIR__ . '/' . $folder . '/folder1/level2',
			__DIR__ . '/' . $folder . '/folder2',
		);
	}

	/**
	 * getFilesRescurive
	 *
	 * @param string $folder
	 *
	 * @return  array
	 */
	public static function getItemsRecursive($folder = 'dest')
	{
		return array (
			__DIR__ . '/' . $folder . '/file2.txt',
			__DIR__ . '/' . $folder . '/folder1',
			__DIR__ . '/' . $folder . '/folder1/level2',
			__DIR__ . '/' . $folder . '/folder1/level2/file3',
			__DIR__ . '/' . $folder . '/folder1/path1',
			__DIR__ . '/' . $folder . '/folder2',
			__DIR__ . '/' . $folder . '/folder2/file2.html',
		);
	}
}
