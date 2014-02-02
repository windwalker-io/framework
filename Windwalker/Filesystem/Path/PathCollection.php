<?php
/**
 * Part of the Windwalker project Filesystem Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Filesystem\Path;

/**
 * A PathLocator collection class
 *
 * @since  1.0
 */
class PathCollection extends \ArrayObject
{
	/**
	 * Paths bag.
	 *
	 * @var array
	 */
	protected $paths = array();

	/**
	 * Paths bag.
	 *
	 * @var array
	 */
	protected $storage = array();

	/**
	 * PathCollection constructor.
	 *
	 * @param  array $paths The PathLocator array.
	 *
	 * @since  1.0
	 */
	public function __construct($paths = array())
	{
		$this->addPaths($paths);

		//parent::__construct($paths);
	}

	/**
	 * Batch add paths to bag.
	 *
	 * @param  mixed $paths Paths to add to path bag, string will be converted to PathLocator object.
	 *
	 * @return  PathCollection  Return this object to support chaining.
	 * @since  1.0
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
	 * @since  1.0
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
			throw new \InvalidArgumentException('PathCollection needed every path element instance of PathLocatorInterface.');
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
	 * @since  1.0
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
	 * @since  1.0
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
	 * @since  1.0
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
	 * Append all paths' iterator into an OutterIterator.
	 *
	 * @param  \Closure $callback Conatains the logic of how to get iterator from path object.
	 *
	 * @return  \AppendIterator  Appended iterators.
	 *
	 * @since  1.0
	 */
	protected function appendIterator(\Closure $callback = null)
	{
		$iterator = new \AppendIterator;

		$paths = (array) parent::getArrayCopy();

		$clousre = function ($path) use ($callback, $iterator)
		{
			return $iterator->append($callback($path));
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
	 * @since  1.0
	 */
	public function getAllChildren($recursive = false)
	{
		return $this->appendIterator(function ($path) use ($recursive)
		{
			return $path->getIterator($recursive);
		});
	}

	/**
	 * Find one file from all paths.
	 *
	 * @param  mixed $condition        Finding condition, that can be a string, a regex or a callback function.
	 *                                 Callback example:
	 *                                 <code>
	 *                                 function($current, $key, $iterator)
	 *                                 {
	 *                                 return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                                 }
	 *                                 </code>
	 * @param  bool $recursive
	 *
	 * @return  \SplFileInfo  Finded file info object.
	 *
	 * @since    1.0
	 */
	public function find($condition, $recursive = false)
	{
		$iterator = $this->appendIterator(function ($path) use ($condition, $recursive)
		{
			return $path->findAll($condition, $recursive);
		});

		$iterator->rewind();

		return $iterator->current();
	}

	/**
	 * Find all files from paths.
	 *
	 * @param  mixed $condition        Finding condition, that can be a string, a regex or a callback function.
	 *                                 Callback example:
	 *                                 <code>
	 *                                 function($current, $key, $iterator)
	 *                                 {
	 *                                 return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
	 *                                 }
	 *                                 </code>
	 * @param bool   $recursive
	 *
	 * @return  \AppendIterator  Finded files or paths iterator.
	 * @since    1.0
	 */
	public function findAll($condition, $recursive = false)
	{
		return $this->appendIterator(function ($path) use ($condition, $recursive)
		{
			return $path->findAll($condition, $recursive);
		});
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
		return $this->appendIterator(function ($path) use ($recursive)
		{
			return $path->getFiles($recursive);
		});
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
		return $this->appendIterator(function ($path) use ($recursive)
		{
			return $path->getFolders($recursive);
		});
	}

	/**
	 * Set prefix to all paths.
	 *
	 * @param  string $prefix The prefix path you want to prepend when path convert to string.
	 *
	 * @return  PathCollection  Return this object to support chaining.
	 * @since  1.0
	 */
	public function setPrefix($prefix)
	{
		foreach ($this as &$path)
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
	 * @since  1.0
	 */
	public function appendAll($appended)
	{
		foreach ($this as &$path)
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
	 * @since  1.0
	 */
	public function prependAll($prepended)
	{

		return $this;
	}

	/**
	 * Convert paths bag to array, and every path to string.
	 *
	 * @param bool $reindex
	 *
	 * @return  array  Raw paths.
	 * @since  1.0
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
	 * When running recrusive scan dir, we have to avoid to re scan same dir.
	 *
	 * @param  PathLocator $path The path to detect is subdir or not.
	 *
	 * @return  boolean  Is subdir or not.
	 * @since  1.0
	 */
	public function isSubdir($path)
	{
		foreach ($this as $member)
		{
			if ($member->isSubdirOf($path))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Automatic call parent methods
	 *
	 * @param   string $name Name of the marhod name we want to gall.
	 * @param          $args
	 *
	 * @return  mixed   The filtered input value.
	 * @since    1.0
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array(parent), array($args));
	}
}