<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem;

use Windwalker\Filesystem\Path\PathCollection;

/**
 * A Path handling class
 *
 * @since  2.0
 */
class Path
{
	/**
	 * Checks if a path's permissions can be changed.
	 *
	 * @param   string  $path  Path to check.
	 *
	 * @return  boolean  True if path can have mode changed.
	 *
	 * @since   2.0
	 */
	public static function canChmod($path)
	{
		$perms = fileperms($path);

		if ($perms !== false)
		{
			if (@chmod($path, $perms ^ 0001))
			{
				@chmod($path, $perms);

				return true;
			}
		}

		return false;
	}

	/**
	 * Chmods files and directories recursively to given permissions.
	 *
	 * @param   string  $path        Root path to begin changing mode [without trailing slash].
	 * @param   string  $filemode    Octal representation of the value to change file mode to [null = no change].
	 * @param   string  $foldermode  Octal representation of the value to change folder mode to [null = no change].
	 *
	 * @return  boolean  True if successful [one fail means the whole operation failed].
	 *
	 * @since   2.0
	 */
	public static function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		// Initialise return value
		$ret = true;

		if (is_dir($path))
		{
			$dh = opendir($path);

			while ($file = readdir($dh))
			{
				if ($file != '.' && $file != '..')
				{
					$fullpath = $path . '/' . $file;

					if (is_dir($fullpath))
					{
						if (!self::setPermissions($fullpath, $filemode, $foldermode))
						{
							$ret = false;
						}
					}
					else
					{
						if (isset($filemode))
						{
							if (!@ chmod($fullpath, octdec($filemode)))
							{
								$ret = false;
							}
						}
					}
				}
			}

			closedir($dh);

			if (isset($foldermode))
			{
				if (!@ chmod($path, octdec($foldermode)))
				{
					$ret = false;
				}
			}
		}
		else
		{
			if (isset($filemode))
			{
				$ret = @ chmod($path, octdec($filemode));
			}
		}

		return $ret;
	}

	/**
	 * Get the permissions of the file/folder at a give path.
	 *
	 * @param   string   $path      The path of a file/folder.
	 * @param   boolean  $toString  Convert permission number to string.
	 *
	 * @return  string  Filesystem permissions.
	 *
	 * @since   2.0
	 */
	public static function getPermissions($path, $toString = false)
	{
		$path = self::clean($path);
		$mode = @ decoct(@ fileperms($path) & 0777);

		if (!$toString)
		{
			return $mode;
		}

		if (strlen($mode) < 3)
		{
			return '---------';
		}

		$parsedMode = '';

		for ($i = 0; $i < 3; $i++)
		{
			// Read
			$parsedMode .= ($mode{$i} & 04) ? "r" : "-";

			// Write
			$parsedMode .= ($mode{$i} & 02) ? "w" : "-";

			// Execute
			$parsedMode .= ($mode{$i} & 01) ? "x" : "-";
		}

		return $parsedMode;
	}

	/**
	 * Checks for snooping outside of the file system root.
	 *
	 * @param   string  $path  A file system path to check.
	 * @param   string  $root  System root path.
	 *
	 * @throws  \Exception
	 * @return  string  A cleaned version of the path or exit on error.
	 *
	 * @since   2.0
	 */
	public static function check($path, $root)
	{
		if (strpos($path, '..') !== false)
		{
			throw new \Exception(__CLASS__ . '::check Use of relative paths not permitted', 20);
		}

		$path = self::clean($path);

		if (($root != '') && strpos($path, self::clean($root)) !== 0)
		{
			throw new \Exception(__CLASS__ . '::check Snooping out of bounds @ ' . $path, 20);
		}

		return $path;
	}

	/**
	 * Function to strip additional / or \ in a path name.
	 *
	 * @param   string  $path  The path to clean.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The cleaned path.
	 *
	 * @since   2.0
	 * @throws  \UnexpectedValueException If $path is not a string.
	 */
	public static function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (!is_string($path))
		{
			throw new \UnexpectedValueException(__CLASS__ . '::clean $path is not a string.');
		}

		$path = trim($path);

		if (($ds == '\\') && ($path[0] == '\\' ) && ( $path[1] == '\\' ))
		// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
		// If dealing with a UNC path don't forget to prepend the path with a backslash.
		{
			$path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
		}
		else
		{
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Normalize a path. This method will do clean() first to replace slashes and remove '..' to create a
	 * Clean path. Unlike realpath(), if this path not exists, normalise() will still return this path.
	 *
	 * @param   string  $path  The path to normalize.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The normalized path.
	 *
	 * @since   2.0.4
	 * @throws  \UnexpectedValueException If $path is not a string.
	 */
	public static function normalize($path, $ds = DIRECTORY_SEPARATOR)
	{
		$parts    = array();
		$path     = static::clean($path, $ds);
		$segments = explode($ds, $path);

		foreach ($segments as $segment)
		{
			if ($segment != '.')
			{
				$test = array_pop($parts);

				if (is_null($test))
				{
					$parts[] = $segment;
				}
				elseif ($segment == '..')
				{
					if ($test == '..')
					{
						$parts[] = $test;
					}

					if ($test == '..' || $test == '')
					{
						$parts[] = $segment;
					}
				}
				else
				{
					$parts[] = $test;
					$parts[] = $segment;
				}
			}
		}

		return implode($ds, $parts);
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param   mixed   $paths  An path string or array of path strings to search in
	 * @param   string  $file   The file name to look for.
	 *
	 * @return  mixed   The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 *
	 * @since   2.0
	 */
	public static function find($paths, $file)
	{
		/**
		 * Files callback
		 *
		 * @param \SplFileInfo                $current  Current item's value
		 * @param string                      $key      Current item's key
		 * @param \RecursiveDirectoryIterator $iterator Iterator being filtered
		 *
		 * @return boolean   TRUE to accept the current item, FALSE otherwise
		 */
		$filter = function($current, $key, $iterator) use ($file)
		{
			return ($current->getBasename() == $file);
		};

		$collection = new PathCollection($paths);

		return $collection->findOne($filter);
	}
}
