<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Query\Oracle;

use Windwalker\Query\Query;

/**
 * Class OracleQuery
 *
 * @since 2.0
 */
class OracleQuery extends Query implements Query\PreparableInterface
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $name = 'oracle';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc.  The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $nameQuote = '"';

	/**
	 * Returns the current dateformat
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $dateFormat = 'RRRR-MM-DD HH24:MI:SS';

	/**
	 * The limit for the result set.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	protected $limit;

	/**
	 * The offset for the result set.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	protected $offset;

	/**
	 * Holds key / value pair of bound objects.
	 *
	 * @var    mixed
	 * @since  2.0
	 */
	protected $bounded = array();

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
	public function bind($key = null, &$value = null, $dataType = \PDO::PARAM_STR, $length = 0, $driverOptions = array())
	{
		// Case 1: Empty Key (reset $bounded array)
		if (empty($key))
		{
			$this->bounded = array();

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

	/**
	 * escape
	 *
	 * @param string $text
	 * @param bool   $extra
	 *
	 * @return  mixed|string
	 */
	public function escape($text, $extra = false)
	{
		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		$text = str_replace("'", "''", $text);

		return addcslashes($text, "\000\n\r\\\032");
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  OracleQuery  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function clear($clause = null)
	{
		switch ($clause)
		{
			case null:
				$this->bounded = array();
				break;
		}

		return parent::clear($clause);
	}

	/**
	 * Method to modify a query already in string format with the needed
	 * additions to make the query limited to a particular number of
	 * results, or start at a particular offset. This method is used
	 * automatically by the __toString() method if it detects that the
	 * query implements the LimitableInterface.
	 *
	 * @param   string  $query  The query in string format
	 * @param   integer $limit  The limit for the result set
	 * @param   integer $offset The offset for the result set
	 *
	 * @return string
	 * @since   2.0
	 */
	public function processLimit($query, $limit, $offset = null)
	{
		// Check if we need to mangle the query.
		if ($limit || $offset)
		{
			$query = "SELECT windwalker2.*
		              FROM (
		                  SELECT windwalker1.*, ROWNUM AS windwalker_db_rownum
		                  FROM (
		                      " . $query . "
		                  ) windwalker1
		              ) windwalker2";

			// Check if the limit value is greater than zero.
			if ($limit > 0)
			{
				$query .= ' WHERE windwalker2.windwalker_db_rownum BETWEEN ' . ($offset + 1) . ' AND ' . ($offset + $limit);
			}
			else
			{
				// Check if there is an offset and then use this.
				if ($offset)
				{
					$query .= ' WHERE windwalker2.windwalker_db_rownum > ' . ($offset + 1);
				}
			}
		}

		return $query;
	}

	/**
	 * Sets the offset and limit for the result set, if the database driver supports it.
	 *
	 * Usage:
	 * $query->setLimit(100, 0); (retrieve 100 rows, starting at first record)
	 * $query->setLimit(50, 50); (retrieve 50 rows, starting at 50th record)
	 *
	 * @param   integer $limit  The limit for the result set
	 * @param   integer $offset The offset for the result set
	 *
	 * @return OracleQuery Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function limit($limit = null, $offset = null)
	{
		$this->limit = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * getDateFormat
	 *
	 * @return  string
	 */
	public function getDateFormat()
	{
		return $this->dateFormat;
	}

	/**
	 * setDateFormat
	 *
	 * @param   string $dateFormat
	 *
	 * @return  OracleQuery  Return self to support chaining.
	 */
	public function setDateFormat($dateFormat)
	{
		$this->dateFormat = $dateFormat;

		return $this;
	}
}
