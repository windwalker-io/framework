<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem;

use Windwalker\Filesystem\Comparator\FileComparatorInterface;
use Windwalker\Filesystem\Iterator\RecursiveDirectoryIterator;

if (!class_exists('CallbackFilterIterator'))
{
	include_once __DIR__ . '/Iterator/CallbackFilterIterator.php';
}

/**
 * Class Filesystem
 *
 * @since 2.0
 */
abstract class Filesystem
{
	/**
	 * copy
	 *
	 * @param string  $src
	 * @param string  $dest
	 * @param bool    $force
	 *
	 * @return  bool
	 */
	public static function copy($src, $dest, $force = false)
	{
		if (is_dir($src))
		{
			Folder::copy($src, $dest, $force);
		}
		elseif (is_file($src))
		{
			File::copy($src, $dest, $force);
		}

		return true;
	}

	/**
	 * move
	 *
	 * @param string  $src
	 * @param string  $dest
	 * @param bool    $force
	 *
	 * @return  bool
	 */
	public static function move($src, $dest, $force = false)
	{
		if (is_dir($src))
		{
			Folder::move($src, $dest, $force);
		}
		elseif (is_file($src))
		{
			File::move($src, $dest, $force);
		}

		return true;
	}

	/**
	 * delete
	 *
	 * @param string $path
	 *
	 * @return  bool
	 */
	public static function delete($path)
	{
		if (is_dir($path))
		{
			Folder::delete($path);
		}
		elseif (is_file($path))
		{
			File::delete($path);
		}

		return true;
	}

	/**
	 * files
	 *
	 * @param   string  $path
	 * @param   bool    $recursive
	 * @param   bool    $toArray
	 *
	 * @return  \CallbackFilterIterator
	 */
	public static function files($path, $recursive = false, $toArray = false)
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
		$callback = function ($current, $key, $iterator)
		{
			return $current->isFile();
		};

		return static::findByCallback($path, $callback, $recursive, $toArray);
	}

	/**
	 * folders
	 *
	 * @param   string  $path
	 * @param   bool    $recursive
	 * @param   boolean $toArray
	 *
	 * @return  \CallbackFilterIterator
	 */
	public static function folders($path, $recursive = false, $toArray = false)
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
		$callback = function ($current, $key, $iterator) use ($path, $recursive)
		{
			if ($recursive)
			{
				// Ignore self
				if ($iterator->getRealPath() == Path::clean($path))
				{
					return false;
				}

				// If set to recursive, every returned folder name will include a dot (.),
				// so we can't using isDot() to detect folder.
				return $iterator->isDir() && ($iterator->getBasename() != '..');
			}
			else
			{
				return $iterator->isDir() && !$iterator->isDot();
			}
		};

		return static::findByCallback($path, $callback, $recursive, $toArray);
	}

	/**
	 * items
	 *
	 * @param   string  $path
	 * @param   bool    $recursive
	 * @param   boolean $toArray
	 *
	 * @return  \CallbackFilterIterator
	 */
	public static function items($path, $recursive = false, $toArray = false)
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
		$callback = function ($current, $key, $iterator) use ($path, $recursive)
		{
			if ($recursive)
			{
				// Ignore self
				if ($iterator->getRealPath() == Path::clean($path))
				{
					return false;
				}

				// If set to recursive, every returned folder name will include a dot (.),
				// so we can't using isDot() to detect folder.
				return ($iterator->getBasename() != '..');
			}
			else
			{
				return !$iterator->isDot();
			}
		};

		return static::findByCallback($path, $callback, $recursive, $toArray);
	}

	/**
	 * Find one file and return.
	 *
	 * @param  string   $path         The directory path.
	 * @param  mixed    $condition    Finding condition, that can be a string, a regex or a callback function.
	 *                                Callback example:
	 *                                <code>
	 *                                function($current, $key, $iterator)
	 *                                {
	 *                                return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                                }
	 *                                </code>
	 * @param  boolean  $recursive    True to resursive.
	 *
	 * @return  \SplFileInfo  Finded file info object.
	 *
	 * @since  2.0
	 */
	public static function findOne($path, $condition, $recursive = false)
	{
		$iterator = new \LimitIterator(static::find($path, $condition, $recursive), 0, 1);

		$iterator->rewind();

		return $iterator->current();
	}

	/**
	 * Find all files which matches condition.
	 *
	 * @param  string   $path       The directory path.
	 * @param  mixed    $condition  Finding condition, that can be a string, a regex or a callback function.
	 *                              Callback example:
	 *                              <code>
	 *                              function($current, $key, $iterator)
	 *                              {
	 *                              return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                              }
	 *                              </code>
	 * @param  boolean  $recursive  True to resursive.
	 * @param  boolean  $toArray    True to convert iterator to array.
	 *
	 * @return  \CallbackFilterIterator  Found files or paths iterator.
	 *
	 * @since  2.0
	 */
	public static function find($path, $condition, $recursive = false, $toArray = false)
	{
		// If conditions is string or array, we make it to regex.
		if (!($condition instanceof \Closure) && !($condition instanceof FileComparatorInterface))
		{
			if (is_array($condition))
			{
				$condition = '/(' . implode('|', $condition) . ')/';
			}
			else
			{
				$condition = '/' . (string) $condition . '/';
			}

			/**
			 * Files callback
			 *
			 * @param \SplFileInfo                $current  Current item's value
			 * @param string                      $key      Current item's key
			 * @param \RecursiveDirectoryIterator $iterator Iterator being filtered
			 *
			 * @return boolean   TRUE to accept the current item, FALSE otherwise
			 */
			$condition = function ($current, $key, $iterator) use ($condition)
			{
				return @preg_match($condition, $iterator->getFilename()) && !$iterator->isDot();
			};
		}
		// If condition is compare object, wrap it with callback.
		elseif ($condition instanceof FileComparatorInterface)
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
			$condition = function ($current, $key, $iterator) use ($condition)
			{
				return $condition->compare($current, $key, $iterator);
			};
		}

		return static::findByCallback($path, $condition, $recursive, $toArray);
	}

	/**
	 * Using a closure function to filter file.
	 *
	 * Reference: http://www.php.net/manual/en/class.callbackfilteriterator.php
	 *
	 * @param  string   $path      The directory path.
	 * @param  \Closure $callback  A callback function to filter file.
	 * @param  boolean  $recursive True to recursive.
	 * @param  boolean  $toArray   True to convert iterator to array.
	 *
	 * @return  \CallbackFilterIterator  Filtered file or path iteator.
	 *
	 * @since  2.0
	 */
	public static function findByCallback($path, \Closure $callback, $recursive = false, $toArray = false)
	{
		$itarator = new \CallbackFilterIterator(static::createIterator($path, $recursive), $callback);

		if ($toArray)
		{
			return static::iteratorToArray($itarator);
		}

		return $itarator;
	}

	/**
	 * Create file iterator of current dir.
	 *
	 * @param  string  $path      The directory path.
	 * @param  boolean $recursive True to recursive.
	 * @param  integer $options   FilesystemIterator Flags provides which will affect the behavior of some methods.
	 *
	 * @throws \InvalidArgumentException
	 * @return  \FilesystemIterator|\RecursiveIteratorIterator  File & dir iterator.
	 */
	public static function createIterator($path, $recursive = false, $options = null)
	{
		$path = Path::clean($path);

		if ($recursive)
		{
			$options = $options ? : (\FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO);
		}
		else
		{
			$options = $options ? : (\FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);
		}

		try
		{
			$iterator = new RecursiveDirectoryIterator($path, $options);
		}
		catch (\UnexpectedValueException $exception)
		{
			throw new \InvalidArgumentException(sprintf('Dir: %s not found.', (string) $path), null, $exception);
		}

		// If rescurive set to true, use RecursiveIteratorIterator
		return $recursive ? new \RecursiveIteratorIterator($iterator) : $iterator;
	}

	/**
	 * iteratorToArray
	 *
	 * @param \Traversable $iterator
	 *
	 * @return  array
	 */
	public static function iteratorToArray(\Traversable $iterator)
	{
		$array = array();

		foreach ($iterator as $key => $file)
		{
			$array[] = (string) $file;
		}

		return $array;
	}
}

