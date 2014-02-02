<?php
/**
 * Part of the Windwalker project Filesystem Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Filesystem\Path;

use Windwalker\Filesystem\Path;
use Windwalker\Filesystem\Comparator\FileComparatorInterface;
use Windwalker\Filesystem\Iterator\RecursiveDirectoryIterator;

// Compat
if (version_compare(PHP_VERSION, 5.4, 'lt'))
{
	\JLoader::register('CallbackFilterIterator', dirname(__DIR__) . '/Iterator/CallbackFilterIterator.php');
}

/**
 * A Path locator class
 *
 * @since  1.0
 */
class PathLocator implements PathLocatorInterface, \IteratorAggregate
{
	/**
	 * Path prefix
	 *
	 * @var string
	 * @since  1.0
	 */
	protected $prefix = '';

	/**
	 * A variable to store paths
	 *
	 * @var array
	 * @since  1.0
	 */
	protected $paths = array();

	/**
	 * Constructor to handle path.
	 *
	 * @param   string $path Path to parse.
	 *
	 * @since   1.0
	 */
	public function __construct($path)
	{
		$this->paths = $this->regularize($path);
	}

	/**
	 * Replace with a new path.
	 *
	 * @param   string $path Path to parse.
	 *
	 * @return  PathLocator  Return this object to support chaining.
	 *
	 * @since  1.0
	 */
	public function redirect($path)
	{
		$this->paths = $this->regularize($path);

		return $this;
	}

	/**
	 * Get file iterator of current dir.
	 *
	 * @param  boolean $recursive True to resursive.
	 *
	 * @return  \FilesystemIterator|\RecursiveIteratorIterator  File & dir iterator.
	 */
	public function getIterator($recursive = false)
	{
		// If we put this object into a foreach, return all files and folders to iterator.
		return $this->findByCallback(function ($current, $key, $iterator)
		{
			return !$iterator->isDot();
		}, $recursive);
	}

	/**
	 * Create file iterator of current dir.
	 *
	 * @param  boolean $recursive True to resursive.
	 *
	 * @throws \InvalidArgumentException
	 * @return  \FilesystemIterator|\RecursiveIteratorIterator  File & dir iterator.
	 */
	public function createIterator($recursive = false)
	{
		try
		{
			$iterator = new RecursiveDirectoryIterator((string) $this);
		}
		catch (\UnexpectedValueException $exception)
		{
			throw new \InvalidArgumentException(sprintf('Dir: %s not found.', (string) $this), null);
		}

		// If rescurive set to true, use RecursiveIteratorIterator
		return $recursive ? new \RecursiveIteratorIterator($iterator) : $iterator;
	}

	/**
	 * Get folder iterator of current dir.
	 *
	 * @param  boolean $recursive True to resursive.
	 *
	 * @return  \CallbackFilterIterator  Iterator only include dirs.
	 */
	public function getFolders($recursive = false)
	{
		return $this->findByCallback(function ($current, $key, $iterator) use ($recursive)
		{
			if ($recursive)
			{
				// If set to recursive, every rturned folder name will include a dot (.),
				// so we can't using isDot() to detect folder.
				return $iterator->isDir() && ($current->getBasename() != '..');
			}
			else
			{
				return $iterator->isDir() && !$iterator->isDot();
			}
		}, $recursive);
	}

	/**
	 * Get file iterator of current dir
	 *
	 * @param  boolean $recursive True to resursive.
	 *
	 * @return  \CallbackFilterIterator  Iterator only include files.
	 */
	public function getFiles($recursive = false)
	{
		return $this->findByCallback(function ($current, $key, $iterator)
		{
			return $iterator->isFile() && !$iterator->isDot();
		}, $recursive);
	}

	/**
	 * Find one file and return.
	 *
	 * @param  mixed   $condition     Finding condition, that can be a string, a regex or a callback function.
	 *                                Callback example:
	 *                                <code>
	 *                                function($current, $key, $iterator)
	 *                                {
	 *                                return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                                }
	 *                                </code>
	 * @param  boolean $recursive     True to resursive.
	 *
	 * @return  \SplFileInfo  Finded file info object.
	 *
	 * @since  1.0
	 */
	public function find($condition, $recursive = false)
	{
		$iterator = new \LimitIterator($this->findAll($condition, $recursive), 0, 1);

		$iterator->rewind();

		return $iterator->current();
	}

	/**
	 * Find all files which matches condition.
	 *
	 * @param  mixed   $condition     Finding condition, that can be a string, a regex or a callback function.
	 *                                Callback example:
	 *                                <code>
	 *                                function($current, $key, $iterator)
	 *                                {
	 *                                return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                                }
	 *                                </code>
	 * @param  boolean $recursive     True to resursive.
	 *
	 * @return  \CallbackFilterIterator  Finded files or paths iterator.
	 *
	 * @since  1.0
	 */
	public function findAll($condition, $recursive = false)
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

			// Create callback
			$condition = function ($current, $key, $iterator) use ($condition)
			{
				return @preg_match($condition, $iterator->getFilename()) && !$iterator->isDot();
			};
		}

		// If condition is campare object, wrap it with callback.
		elseif ($condition instanceof FileComparatorInterface)
		{
			// Create callback
			$condition = function ($current, $key, $iterator) use ($condition)
			{
				return $condition->compare($current, $key, $iterator);
			};
		}

		return $this->findByCallback($condition, $recursive);
	}

	/**
	 * Using a closure function to filter file.
	 * Reference: http://www.php.net/manual/en/class.callbackfilteriterator.php
	 *
	 * @param  \Closure $callback  A callback function to filter file.
	 * @param  boolean  $recursive True to resursive.
	 *
	 * @return  \CallbackFilterIterator  Filtered file or path iteator.
	 *
	 * @since  1.0
	 */
	public function findByCallback(\Closure $callback, $recursive = false)
	{
		return new \CallbackFilterIterator($this->createIterator($recursive), $callback);
	}

	/**
	 * Regularize path, remove not necessary elements.
	 *
	 * @param  string      $path         A given path to regularize.
	 * @param  bool|string $returnString Return string or array.
	 *
	 * @return  string|array  Regularized path.
	 * @since  1.0
	 */
	protected function regularize($path, $returnString = false)
	{
		// Clean the Directory separator 
		$path = $this->clean($path);

		// Extract to array
		$path = $this->extract($path);

		// Remove dots from path
		$path = $this->removeDots($path);

		// If set to return string, compact it.
		if ($returnString == true)
		{
			$path = $this->compact($path);
		}

		return $path;
	}

	/**
	 * Clean path and remove dots.
	 *
	 * @param   string $path A given path to parse.
	 *
	 * @return  string  Cleaned path.
	 *
	 * @since  1.0
	 */
	protected function clean($path)
	{
		$path = rtrim($path, ' /\\');

		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);

		return $path;
	}

	/**
	 * Remove dots from path.
	 *
	 * @param  string|array $path A given path to remove dots.
	 *
	 * @return  string|array  Cleaned path.
	 *
	 * @since  1.0
	 */
	protected function removeDots($path)
	{
		$isBeginning = true;

		// If not array, extract it.
		$isArray = is_array($path);

		$path = $isArray ? $path : $this->extract($path);

		// Search for dot files
		foreach ($path as $key => $row)
		{
			// Remove dot files
			if ($row == '.')
			{
				unset($path[$key]);
			}

			// Remove dots and go parent dir
			if ($row == '..' && !$isBeginning)
			{
				unset($path[$key]);
				unset($path[$key - 1]);
			}

			// Do not get parent if dots in the beginning
			if ($row != '..' && $isBeginning)
			{
				$isBeginning = false;
			}
		}

		// Re index array
		$path = array_values($path);

		return $isArray ? $path : $this->compact($path);
	}

	/**
	 * Detect is current path a dir?
	 *
	 * @return  boolean  True if is a dir.
	 *
	 * @since  1.0
	 */
	public function isDir()
	{
		return is_dir((string) $this);
	}

	/**
	 * Detect is current path a file?
	 *
	 * @return  boolean  True if is a file.
	 *
	 * @since  1.0
	 */
	public function isFile()
	{
		return is_file((string) $this);
	}

	/**
	 * Detect is current path exists?
	 *
	 * @return  boolean  True if exists.
	 *
	 * @since  1.0
	 */
	public function exists()
	{
		return file_exists((string) $this);
	}

	/**
	 * Set a prefix, when this object convert to string,
	 * prefix will auto add to the front of path.
	 *
	 * @param   string $prefix Prefix string to set.
	 *
	 * @return  PathLocator  Return this object to support chaining.
	 *
	 * @since  1.0
	 */
	public function setPrefix($prefix = '')
	{
		$this->prefix = $this->regularize($prefix, true);

		return $this;
	}

	/**
	 * Get a child path of given name.
	 *
	 * @param   string $name Child name.
	 *
	 * @return  PathLocator  Return this object to support chaining.
	 *
	 * @since  1.0
	 */
	public function child($name)
	{
		$path = $this->regularize($name);

		$this->append($path);

		return $this;
	}

	/**
	 * Get a parent path of given condition.
	 *
	 * @param null $condition Parent condition.
	 *
	 * @return  PathLocator  Return this object to support chaining.
	 *
	 * @since    1.0
	 */
	public function parent($condition = null)
	{
		// Up one level
		if (is_null($condition))
		{
			array_pop($this->paths);
		}
		// Up mutiple level
		elseif (is_int($condition))
		{
			$this->paths = array_slice($this->paths, 0, -$condition);
		}
		// Find a dir name and go to this level
		elseif (is_string($condition))
		{
			$paths = $this->paths;

			$paths = array_reverse($paths);

			// Find parent
			$n = 0;

			foreach ($paths as $key => $name)
			{
				if ($key == 0)
				{
					// Ignore latest dir
					continue;
				}

				// Is this dir match condition?
				if ($name == $condition)
				{
					$n = $key;
					break;
				}
			}

			$this->paths = array_slice($this->paths, 0, -$n);
		}

		return $this;
	}

	/**
	 * Append a new path before current path.
	 *
	 * @param   string $path Path to append.
	 *
	 * @return  PathLocator  Return this object to support chaining.
	 *
	 * @since  1.0
	 */
	public function append($path)
	{
		if (!is_array($path))
		{
			$path = $this->regularize($path);
		}

		$path = array_merge($this->paths, $path);

		$this->paths = $this->removeDots($path);

		return $this;
	}

	/**
	 * Is this path subdir of given path?
	 *
	 * @param  string $parent Given path to detect.
	 *
	 * @return  boolean  Is subdir or not.
	 *
	 * @since  1.0
	 */
	public function isSubdirOf($parent)
	{
		$self = (string) $this;

		$parent = $this->regularize($parent, true);

		// Path is self
		if ($self == $parent)
		{
			return false;
		}

		// Path is parent
		if (strpos($parent, $self) === 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Convert this object to string.
	 *
	 * @return  string  Path name.
	 *
	 * @since  1.0
	 */
	public function __toString()
	{
		$path = $this->compact($this->paths);

		if ($this->prefix)
		{
			$path = rtrim($this->prefix, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
		}

		$path = $this->removeDots($path);

		return $path;
	}

	/**
	 * Explode path by DIRECTORY_SEPARATOR.
	 *
	 * @param   string $path Path to extract.
	 *
	 * @return  array  Extracted path array.
	 *
	 * @since  1.0
	 */
	protected function extract($path)
	{
		return explode(DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * Implode path by DIRECTORY_SEPARATOR.
	 *
	 * @param   string $path Path to compact.
	 *
	 * @return  array  Compacted path array.
	 *
	 * @since  1.0
	 */
	protected function compact($path)
	{
		return implode(DIRECTORY_SEPARATOR, $path);
	}
}