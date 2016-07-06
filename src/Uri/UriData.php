<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Uri;

use Traversable;

/**
 * The UriData class.
 *
 * @property string full
 * @property string current
 * @property string script
 * @property string root
 * @property string route
 * @property string host
 * @property string path
 * @property string scheme
 * @property string isSsl
 * @property int    port
 *
 * @since  3.0-beta2
 */
class UriData implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * Property full.
	 *
	 * @var  string
	 */
	public $full;

	/**
	 * Property current.
	 *
	 * @var  string
	 */
	public $current;

	/**
	 * Property script.
	 *
	 * @var  string
	 */
	public $script;

	/**
	 * Property root.
	 *
	 * @var  string
	 */
	public $root;

	/**
	 * Property route.
	 *
	 * @var  string
	 */
	public $route;

	/**
	 * Property host.
	 *
	 * @var  string
	 */
	public $host;

	/**
	 * Property path.
	 *
	 * @var  string
	 */
	public $path;

	/**
	 * UriData constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data = array())
	{
		if ($data)
		{
			foreach ((array) $data as $key => $value)
			{
				if (property_exists($this, $key))
				{
					$this->$key = (string) $value;
				}
			}
		}
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return Traversable An instance of an object implementing Iterator or Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator(get_object_vars($this));
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean True on success or false on failure.
	 *                 The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}

	/**
	 * Count elements of an object
	 *
	 * @return int The custom count as an integer.
	 *             The return value is cast to an integer.
	 */
	public function count()
	{
 		return count(get_object_vars($this));
	}
}
