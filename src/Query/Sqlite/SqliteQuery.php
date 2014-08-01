<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Query\Sqlite;

use Windwalker\Query\Query;
use Windwalker\Query\Query\PreparableTrait;

/**
 * Class SqliteQuery
 *
 * @since 1.0
 */
class SqliteQuery extends Query
{
	use PreparableTrait;

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'sqlite';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nameQuote = '`';

	/**
	 * Method to escape a string for usage in an SQLite statement.
	 *
	 * Note: Using query objects with bound variables is preferable to the below.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Unused optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   1.0
	 */
	public function escape($text, $extra = false)
	{
		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		if (!is_callable('SQLite3', 'escapeString'))
		{
			return $this->escapeWithNoConnection($text);
		}

		return \SQLite3::escapeString($text);
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  SqliteQuery  Returns this object to allow chaining.
	 *
	 * @since   1.0
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
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function processLimit($query, $limit, $offset = 0)
	{
		if ($limit > 0 || $offset > 0)
		{
			$query .= ' LIMIT ' . $offset . ', ' . $limit;
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
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  SqliteQuery  Returns this object to allow chaining.
	 *
	 * @since   1.0
	 */
	public function limit($limit = 0, $offset = 0)
	{
		$this->limit = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}
}

