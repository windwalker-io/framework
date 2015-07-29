<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem\Path;

use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Iterator\ArrayObject;
use Windwalker\Filesystem\Path;

if (!class_exists('CallbackFilterIterator'))
{
	include_once __DIR__ . '/../Iterator/CallbackFilterIterator.php';
}

/**
 * A PathLocator collection class
 *
 * @since  2.0
 */
class PathCollection extends ArrayObject
{
	/**
	 * Paths bag.
	 *
	 * @var PathLocator[]
	 */
	protected $storage = array();

	/**
	 * PathCollection constructor.
	 *
	 * @param  array $paths The PathLocator array.
	 *
	 * @since  2.0
	 */
	public function __construct($paths = array())
	{
		$this->addPaths($paths);
	}

	/**
	 * Batch add paths to bag.
	 *
	 * @param  mixed $paths Paths to add to path bag, string will be converted to PathLocator object.
	 *
	 * @return  PathCollection  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function addPaths($paths)
	{
		$paths = is_array($paths) ? $paths : array($paths);

		foreach ($paths as $key => $path)
		{
			$key = is_int($key) ? null : $key;

			$this->addPath($path, $key);
		}

		return $this;
	}

	/**
	 * Add one path to bag.
	 *
	 * @param   mixed  $path   The path your want to store in bag,
	 *                         have to be a string or PathLocator object.
	 * @param   string $key    Path key, useful when you want to remove a path.
	 *
	 * @throws \InvalidArgumentException
	 * @return  PathCollection  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function addPath($path, $key = null)
	{
		// If path element is subclass of PathLocatorInterface, just put it in path bag.
		// You can create any your Path locator class implements from PathLocatorInterface.
		if ($path instanceof PathLocatorInterface)
		{
			// Nothing to do
		}
		// If this element is a path string, we create a PathLocator to wrap it.
		elseif (is_string($path) || !$path)
		{
			$path = new PathLocator($path);
		}
		// If type of this element not match our interface, throw exception.
		else
		{
			throw new \InvalidArgumentException('PathCollection need every path element instance of PathLocatorInterface.');
		}

		if ($key)
		{
			parent::offsetSet($key, $path);
		}
		else
		{
			parent::append($path);
		}

		return $this;
	}

	/**
	 * Using key to remove a path from bag.
	 *
	 * @param   string $key The key of path you want to remove.
	 *
	 * @return  PathCollection  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function removePath($key)
	{
		parent::offsetUnset($key);

		return $this;
	}

	/**
	 * Get all paths with key from bag.
	 *
	 * @return  array  An array includes all path objects.
	 *
	 * @since  2.0
	 */
	public function getPaths()
	{
		return parent::getArrayCopy();
	}

	/**
	 * Using key to get a path.
	 *
	 * @param  string $key       The key of path you want to get.
	 * @param  string $default   If path not exists, return this default path.
	 *                           Default value can be PathLocator object string or null.
	 *                           String will auto wrapped by object, if is null, just return null.
	 *
	 * @return  PathLocator  The path which you want.
	 *
	 * @since  2.0
	 */
	public function getPath($key, $default = null)
	{
		if (!parent::offsetExists($key))
		{
			if (!$default)
			{
				return $default;
			}

			if (!($default instanceof PathLocatorInterface))
			{
				$default = new PathLocator($default);
			}

			return $default;
		}

		return parent::offsetGet($key);
	}

	/**
	 * Append all paths' iterator into an OuterIterator.
	 *
	 * @param  \Closure $callback Contains the logic of how to get iterator from path object.
	 *
	 * @return  \AppendIterator  Appended iterators.
	 *
	 * @since  2.0
	 */
	protected function appendIterator(\Closure $callback = null)
	{
		$iterator = new \AppendIterator;

		$paths = (array) parent::getArrayCopy();

		$clousre = function ($path) use ($callback, $iterator)
		{
			$iterator->append($callback($path));
		};

		foreach ($this as $path)
		{
			if ($this->isSubdir($path))
			{
				continue;
			}

			$clousre($path);
		}

		return $iterator;
	}

	/**
	 * Get all files and folders as an iterator.
	 *
	 * @param  boolean $recursive True to support recrusive.
	 *
	 * @return  \AppendIterator  An OutterIterator contains all paths' iterator.
	 *
	 * @since  2.0
	 */
	public function getAllChildren($recursive = false)
	{
		return $this->appendIterator(
			function ($path) use ($recursive)
			{
				return $path->getIterator($recursive);
			}
		);
	}

	/**
	 * Find one file from all paths.
	 *
	 * @param  mixed   $condition      Finding condition, that can be a string, a regex or a callback function.
	 *                                 Callback example:
	 *                                 <code>
	 *                                 function($current, $key, $iterator)
	 *                                 {
	 *                                 return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                                 }
	 *                                 </code>
	 * @param  boolean $recursive      True to resursive.
	 *
	 * @return  \SplFileInfo  Finded file info object.
	 *
	 * @since  2.0
	 */
	public function findOne($condition, $recursive = false)
	{
		$iterator = $this->appendIterator(
			function ($path) use ($condition, $recursive)
			{
				return Filesystem::find((string) $path, $condition, $recursive);
			}
		);

		$iterator->rewind();

		return $iterator->current();
	}

	/**
	 * Find all files from paths.
	 *
	 * @param  mixed   $condition      Finding condition, that can be a string, a regex or a callback function.
	 *                                 Callback example:
	 *                                 <code>
	 *                                 function($current, $key, $iterator)
	 *                                 {
	 *                                 return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                                 }
	 *                                 </code>
	 * @param  boolean $recursive      True to resursive.
	 *
	 * @return  \AppendIterator  Finded files or paths iterator.
	 *
	 * @since  2.0
	 */
	public function find($condition, $recursive = false)
	{
		return $this->appendIterator(
			function ($path) use ($condition, $recursive)
			{
				return Filesystem::find((string) $path, $condition, $recursive);
			}
		);
	}

	/**
	 * Get file iterator of all paths
	 *
	 * @param  boolean $recursive True to resursive.
	 *
	 * @return  \AppendIterator  Iterator only include files.
	 */
	public function getFiles($recursive = false)
	{
		return $this->appendIterator(
			function ($path) use ($recursive)
			{
				return Filesystem::files((string) $path, $recursive);
			}
		);
	}

	/**
	 * Get folder iterator of all paths
	 *
	 * @param  boolean $recursive True to resursive.
	 *
	 * @return  \AppendIterator  Iterator only include dirs.
	 */
	public function getFolders($recursive = false)
	{
		return $this->appendIterator(
			function ($path) use ($recursive)
			{
				return Filesystem::folders((string) $path, $recursive);
			}
		);
	}

	/**
	 * Set prefix to all paths.
	 *
	 * @param  string $prefix The prefix path you want to prepend when path convert to string.
	 *
	 * @return  PathCollection  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function setPrefix($prefix)
	{
		foreach ($this->storage as &$path)
		{
			$path->setPrefix((string) $prefix);
		}

		return $this;
	}

	/**
	 * Append a new path to all paths.
	 *
	 * @param   string $appended Path to append.
	 *
	 * @return  PathCollection  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function appendAll($appended)
	{
		foreach ($this->storage as &$path)
		{
			$path->append($appended);
		}

		return $this;
	}

	/**
	 * Prepend a new path to all paths.
	 *
	 * @param  string $prepended Path to prepend.
	 *
	 * @return  PathCollection  Return this object to support chaining.
	 *
	 * @since  2.0
	 */
	public function prependAll($prepended)
	{
		foreach ($this->storage as &$path)
		{
			$path->prepend($prepended);
		}

		return $this;
	}

	/**
	 * Convert paths bag to array, and every path to string.
	 *
	 * @param bool $reindex
	 *
	 * @return  array  Raw paths.
	 *
	 * @since  2.0
	 */
	public function toArray($reindex = false)
	{
		$array = array();

		foreach ($this as $key => $path)
		{
			if ($reindex)
			{
				$array[] = (string) clone $path;
			}
			else
			{
				$array[$key] = (string) clone $path;
			}
		}

		return $array;
	}

	/**
	 * Is this path a subdir of another path in bag?
	 *
	 * When running recursive scan dir, we have to avoid to re scan same dir.
	 *
	 * @param  PathLocator $path The path to detect is subdir or not.
	 *
	 * @return  boolean  Is subdir or not.
	 *
	 * @since  2.0
	 */
	public function isSubdir($path)
	{
		foreach ($this->storage as $member)
		{
			if ($member->isSubdirOf($path))
			{
				return true;
			}
		}

		return false;
	}
}
