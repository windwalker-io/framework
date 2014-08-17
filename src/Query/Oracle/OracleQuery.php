<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Query\Oracle;

use Windwalker\Query\Query;

/**
 * Class OracleQuery
 *
 * @since {DEPLOY_VERSION}
 */
class OracleQuery extends Query
{
	use Query\PreparableTrait;

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
	 */
	public $name = 'oracle';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc.  The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
	 */
	protected $nameQuote = '"';

	/**
	 * Returns the current dateformat
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
	 */
	protected $dateFormat = 'RRRR-MM-DD HH24:MI:SS';

	/**
	 * The limit for the result set.
	 *
	 * @var    integer
	 * @since  {DEPLOY_VERSION}
	 */
	protected $limit;

	/**
	 * The offset for the result set.
	 *
	 * @var    integer
	 * @since  {DEPLOY_VERSION}
	 */
	protected $offset;

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
	 * @since   {DEPLOY_VERSION}
	 */
	public function processLimit($query, $limit, $offset = 0)
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
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  OracleQuery  Returns this object to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function limit($limit = 0, $offset = 0)
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

