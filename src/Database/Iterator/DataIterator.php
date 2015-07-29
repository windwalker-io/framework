<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Iterator;

use Windwalker\Database\Command\AbstractReader;

/**
 * Class DataIterator
 *
 * @since 2.0
 */
class DataIterator implements \Countable, \Iterator
{
	/**
	 * Property reader.
	 *
	 * @var  AbstractReader
	 */
	protected $reader = null;

	/**
	 * Property key.
	 *
	 * @var  int
	 */
	protected $key = -1;

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
	 * @param AbstractReader $reader
	 * @param string         $class
	 */
	public function __construct(AbstractReader $reader, $class = '\\stdClass')
	{
		$this->reader = $reader;
		$this->class = $class;

		$this->next();
	}

	/**
	 * Database iterator destructor.
	 *
	 * @since   2.0
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
		return $this->reader->count();
	}
}

