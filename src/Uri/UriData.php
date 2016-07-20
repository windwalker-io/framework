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
 * @method string full()
 * @method string current()
 * @method string script($uri = null)
 * @method string root($uri = null)
 * @method string route()
 * @method string host($uri = null)
 * @method string path($uri = null)
 * @method string scheme()
 *
 * @since  3.0
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
	 * __call
	 *
	 * @param   string  $name
	 * @param   array   $args
	 *
	 * @return  mixed
	 */
	public function __call($name, $args)
	{
		if (property_exists($this, $name))
		{
			if (isset($args[0]))
			{
				return $this->addPrefix($name, $args[0]);
			}

			return $this->$name;
		}

		throw new \BadMethodCallException('Method: ' . __CLASS__ . '::' . $name . '() not found.');
	}

	/**
	 * createUri
	 *
	 * @param   string  $uri
	 *
	 * @return  Uri
	 */
	public static function createUri($uri)
	{
		return new Uri($uri);
	}

	/**
	 * createPsrUri
	 *
	 * @param   string  $uri
	 *
	 * @return  PsrUri
	 */
	public static function createPsrUri($uri)
	{
		return new PsrUri($uri);
	}

	/**
	 * addPrefix
	 *
	 * @param   string  $name
	 * @param   string  $url
	 *
	 * @return  string
	 */
	public function addPrefix($name, $url)
	{
		return $this->$name . '/' . $url;
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
