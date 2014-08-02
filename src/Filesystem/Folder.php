<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Filesystem;

use Windwalker\Filesystem\Exception\FilesystemException;

/**
 * A Folder handling class
 *
 * @since  1.0
 */
abstract class Folder
{
	/**
	 * Copy a folder.
	 *
	 * @param   string   $src    The path to the source folder.
	 * @param   string   $dest   The path to the destination folder.
	 * @param   boolean  $force  Force copy.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 */
	public static function copy($src, $dest, $force = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// Eliminate trailing directory separators, if any
		$src = rtrim($src, '/\\');
		$dest = rtrim($dest, '/\\');

		if (!is_dir($src))
		{
			throw new FilesystemException('Source folder not found', -1);
		}

		if (is_dir($dest) && !$force)
		{
			throw new FilesystemException('Destination folder exists', -1);
		}

		// Make sure the destination exists
		if (!static::create($dest))
		{
			throw new FilesystemException('Cannot create destination folder', -1);
		}

		$sources = static::items($src, true, false);

		// Walk through the directory copying files and recursing into folders.
		foreach ($sources as $file)
		{
			$srcFile = $src . '/' . $file;
			$destFile = $dest . '/' . $file;

			if (is_dir($srcFile))
			{
				static::create($destFile);
			}
			elseif (is_file($srcFile))
			{
				File::copy($srcFile, $destFile);
			}
		}

		return true;
	}

	/**
	 * Create a folder -- and all necessary parent folders.
	 *
	 * @param   string   $path  A path to create from the base path.
	 * @param   integer  $mode  Directory permissions to set for folders created. 0755 by default.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 */
	public static function create($path = '', $mode = 0755)
	{
		// Check to make sure the path valid and clean
		$path = Path::clean($path);

		// Check if dir already exists
		if (is_dir($path))
		{
			return true;
		}

		// We need to get and explode the open_basedir paths
		$obd = ini_get('open_basedir');

		// If open_basedir is set we need to get the open_basedir that the path is in
		if ($obd != null)
		{
			$obdSeparator = defined('PHP_WINDOWS_VERSION_MAJOR') ? ";" : ":";

			// Create the array of open_basedir paths
			$obdArray = explode($obdSeparator, $obd);
			$inBaseDir = false;

			// Iterate through open_basedir paths looking for a match
			foreach ($obdArray as $test)
			{
				$test = Path::clean($test);

				if (strpos($path, $test) === 0)
				{
					$inBaseDir = true;
					break;
				}
			}

			if ($inBaseDir == false)
			{
				// Throw a FilesystemException because the path to be created is not in open_basedir
				throw new FilesystemException(__METHOD__ . ': Path not in open_basedir paths');
			}
		}

		$path = explode(DIRECTORY_SEPARATOR, $path);

		$dir = array_shift($path);

		foreach ($path as $folder)
		{
			$dir .= DIRECTORY_SEPARATOR . $folder;

			if (is_dir($dir))
			{
				continue;
			}

			// First set umask
			$origmask = @umask(0);

			// Create the path
			if (!@mkdir($dir, $mode))
			{
				@umask($origmask);

				throw new FilesystemException(__METHOD__ . ': Could not create directory.  Path: ' . $dir);
			}

			// Reset umask
			@umask($origmask);
		}

		return true;
	}

	/**
	 * Delete a folder.
	 *
	 * @param   string  $path  The path to the folder to delete.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 * @throws  \UnexpectedValueException
	 */
	public static function delete($path)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// Sanity check
		if (!rtrim($path, '/\\'))
		{
			// Bad programmer! Bad Bad programmer!
			throw new FilesystemException(__METHOD__ . ': You can not delete a base directory.');
		}

		// Check to make sure the path valid and clean
		$path = Path::clean($path);

		// Is this really a folder?
		if (!is_dir($path))
		{
			throw new \UnexpectedValueException(sprintf('%1$s: Path is not a folder. Path: %2$s', __METHOD__, $path));
		}

		// Remove all the files in folder if they exist; disable all filtering
		$files = static::files($path);

		if (!empty($files))
		{
			File::delete($files);
		}

		// Remove sub-folders of folder; disable all filtering
		$folders = static::folders($path);

		foreach ($folders as $folder)
		{
			if (is_link($folder))
			{
				// Don't descend into linked directories, just delete the link.
				File::delete($folder);
			}
			else
			{
				static::delete($folder);
			}
		}

		// In case of restricted permissions we zap it one way or the other
		// as long as the owner is either the webserver or the ftp.
		if (@rmdir($path))
		{
			return true;
		}
		else
		{
			throw new FilesystemException(sprintf('%1$s: Could not delete folder. Path: %2$s', __METHOD__, $path));
		}
	}

	/**
	 * Moves a folder.
	 *
	 * @param   string $src       The path to the source folder.
	 * @param   string $dest      The path to the destination folder.
	 * @param   bool   $override  Override files.
	 *
	 * @throws Exception\FilesystemException
	 * @return  mixed  Error message on false or boolean true on success.
	 *
	 * @since    1.0
	 */
	public static function move($src, $dest, $override = false)
	{
		if (!is_dir($src))
		{
			throw new FilesystemException('Cannot find source folder');
		}

		if (is_dir($dest))
		{
			if (!$override)
			{
				throw new FilesystemException('Folder already exists');
			}

			foreach (static::items($src, true, false) as $item)
			{
				if (is_file($src . '/' . $item))
				{
					File::move($src . '/' . $item, $dest . '/' . $item, true);
				}
				elseif (is_dir($src . '/' . $item))
				{
					static::create($dest . '/' . $item);
				}
			}

			static::delete($src);

			return true;
		}

		if (!@rename($src, $dest))
		{
			throw new FilesystemException('Rename failed');
		}

		return true;
	}

	public static function files($path, $recursive = false, $fullName = true)
	{
		$files = array();

		/** @var $file \SplFileInfo */
		foreach (Filesystem::files($path, $recursive) as $file)
		{
			if ($fullName)
			{
				$name = $file->getPathname();
			}
			else
			{
				$name = $file->getBasename();
			}

			$files[] = $name;
		}

		return $files;
	}

	public static function items($path, $recursive = false, $fullName = true)
	{
		$files = array();
		$pathLength = strlen($path);

		/** @var $file \SplFileInfo */
		foreach (Filesystem::items($path, $recursive) as $file)
		{
			if ($fullName)
			{
				$name = $file->getPathname();
			}
			else
			{
				$name = $file->getRealPath();

				$name = trim(substr($name, $pathLength), DIRECTORY_SEPARATOR);
			}

			$files[] = $name;
		}

		return $files;
	}

	public static function folders($path, $recursive = false, $fullName = true)
	{
		$files = array();
		$pathLength = strlen($path);

		/** @var $file \SplFileInfo */
		foreach (Filesystem::folders($path, $recursive) as $file)
		{
			if ($fullName)
			{
				$name = $file->getPathname();
			}
			else
			{
				$name = $file->getRealPath();

				$name = trim(substr($name, $pathLength), DIRECTORY_SEPARATOR);
			}

			$files[] = $name;
		}

		return $files;
	}

	/**
	 * Lists folder in format suitable for tree display.
	 *
	 * @param   string   $path      The path of the folder to read.
	 * @param   integer  $maxLevel  The maximum number of levels to recursively read, defaults to three.
	 * @param   integer  $level     The current level, optional.
	 * @param   integer  $parent    Unique identifier of the parent folder, if any.
	 *
	 * @return  array  Folders in the given folder.
	 *
	 * @since   1.0
	 */
	public static function listFolderTree($path, $maxLevel = 3, $level = 0, $parent = 0)
	{
		$dirs = array();

		if ($level == 0)
		{
			$GLOBALS['_JFolder_folder_tree_index'] = 0;
		}

		if ($level < $maxLevel)
		{
			$folders = static::folders($path, false, false);

			// First path, index foldernames
			foreach ($folders as $name)
			{
				$id = ++$GLOBALS['_JFolder_folder_tree_index'];
				$fullName = Path::clean($path . '/' . $name);

				$dirs[] = array('id' => $id, 'parent' => $parent, 'name' => $name, 'fullname' => $fullName,
					'relname' => str_replace(JPATH_ROOT, '', $fullName));

				$dirs2 = self::listFolderTree($fullName, $maxLevel, $level + 1, $id);

				$dirs = array_merge($dirs, $dirs2);
			}
		}

		return $dirs;
	}

	/**
	 * Makes path name safe to use.
	 *
	 * @param   string  $path  The full path to sanitise.
	 *
	 * @return  string  The sanitised string.
	 *
	 * @since   1.0
	 */
	public static function makeSafe($path)
	{
		$regex = array('#[^A-Za-z0-9_\\\/\(\)\[\]\{\}\#\$\^\+\.\'~`!@&=;,-]#');

		return preg_replace($regex, '', $path);
	}
}
