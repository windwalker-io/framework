<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database;

use Windwalker\Database\Command\DatabaseReader;

/**
 * Class DataIterator
 *
 * @since 1.0
 */
class DataIterator implements \Countable, \Iterator
{
	/**
	 * Property reader.
	 *
	 * @var  DatabaseReader
	 */
	protected $reader = null;

	/**
	 * Property key.
	 *
	 * @var  int
	 */
	protected $key = 0;

	/**
	 * Property current.
	 *
	 * @var object
	 */
	protected $current;
	/**
	 * Property class.
	 *
	 * @var  string
	 */
	protected $class;

	/**
	 * Constructor.
	 *
	 * @param DatabaseReader $reader
	 * @param string         $class
	 */
	public function __construct(DatabaseReader $reader, $class = '\\stdClass')
	{
		$this->reader = $reader;
		$this->class = $class;

		$this->next();
	}

	/**
	 * Database iterator destructor.
	 *
	 * @since   1.0
	 */
	public function __destruct()
	{
		$this->reader->freeResult();
	}

	/**
	 * Return the current element
	 *
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * Move forward to next element
	 *
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		// Try to get an object
		$this->current = $current = $this->reader->fetchObject($this->class);

		if ($current)
		{
			$this->key++;
		}
	}

	/**
	 * Return the key of the current element
	 *
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key()
	{
		return $this->key;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 *       Returns true on success or false on failure.
	 */
	public function valid()
	{
		return (boolean) $this->current();
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
	}

	/**
	 * Count elements of an object
	 *
	 * @return int The custom count as an integer.
	 */
	public function count()
	{
		$this->reader->count();
	}
}
 