<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Query\Sqlite;

use Windwalker\Query\Query;
use Windwalker\Query\Query\PreparableTrait;

/**
 * Class SqliteQuery
 *
 * @since {DEPLOY_VERSION}
 */
class SqliteQuery extends Query
{
	use PreparableTrait;

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
	 */
	public $name = 'sqlite';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
}

