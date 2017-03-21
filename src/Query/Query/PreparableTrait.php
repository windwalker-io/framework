<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Query;

/**
 * Class PreparableTrait
 *
 * @since 2.0
 */
trait PreparableTrait
{
	/**
	 * Holds key / value pair of bound objects.
	 *
	 * @var    mixed
	 * @since  2.0
	 */
	protected $bounded = [];

	/**
	 * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query execution. Also
	 * removes a variable that has been bounded from the internal bounded array when the passed in value is null.
	 *
	 * @param   string|integer  $key            The key that will be used in your SQL query to reference the value. Usually of
	 *                                          the form ':key', but can also be an integer.
	 * @param   mixed           &$value         The value that will be bound. The value is passed by reference to support output
	 *                                          parameters such as those possible with stored procedures.
	 * @param   integer         $dataType       Constant corresponding to a SQL datatype.
	 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters.
	 * @param   array           $driverOptions  Optional driver options to be used.
	 *
	 * @return  static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function bind($key = null, &$value = null, $dataType = \PDO::PARAM_STR, $length = 0, $driverOptions = [])
	{
		// Case 1: Empty Key (reset $bounded array)
		if (empty($key))
		{
			$this->bounded = [];

			return $this;
		}

		// Case 2: Key Provided, null value (unset key from $bounded array)
		if (is_null($value))
		{
			if (isset($this->bounded[$key]))
			{
				unset($this->bounded[$key]);
			}

			return $this;
		}

		$obj = new \stdClass;

		$obj->value = &$value;
		$obj->dataType = $dataType;
		$obj->length = $length;
		$obj->driverOptions = $driverOptions;

		// Case 3: Simply add the Key/Value into the bounded array
		$this->bounded[$key] = $obj;

		return $this;
	}

	/**
	 * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then that item is
	 * returned.
	 *
	 * @param   mixed  $key  The bounded variable key to retrieve.
	 *
	 * @return  mixed
	 *
	 * @since   2.0
	 */
	public function &getBounded($key = null)
	{
		if (empty($key))
		{
			return $this->bounded;
		}
		else
		{
			if (isset($this->bounded[$key]))
			{
				return $this->bounded[$key];
			}
		}

		return null;
	}
}
