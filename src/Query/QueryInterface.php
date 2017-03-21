<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query;


interface QueryInterface
{
	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string	The completed query.
	 *
	 * @since   2.0
	 */
	public function __toString();

	/**
	 * Get clause  value.
	 *
	 * @param   string  $clause  Get query clause.
	 *
	 * @return  QueryElement|mixed
	 */
	public function get($clause);

	/**
	 * Add a single column, or array of columns to the CALL clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The call method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->call('a.*')->call('b.id');
	 * $query->call(array('a.*', 'b.id'));
	 *
	 * @param   mixed  $columns  A string or an array of field names.
	 *
	 * @return  static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function call($columns);

	/**
	 * Adds a column, or array of column names that would be used for an INSERT INTO statement.
	 *
	 * @param   mixed  $columns  A column name, or array of column names.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function columns($columns);

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the getDateFormat method directly.
	 *
	 * @return  string  The format string.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function dateFormat();

	/**
	 * Add a table name to the DELETE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->delete('#__a')->where('id = 1');
	 *
	 * @param   string  $table  The name of the table to delete from.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function delete($table = null);

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the escape method directly.
	 *
	 * Note that 'e' is an alias for this method as it is in JDatabaseDatabaseDriver.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function escape($text, $extra = false);

	/**
	 * Add a single column, or array of columns to the EXEC clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The exec method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->exec('a.*')->exec('b.id');
	 * $query->exec(array('a.*', 'b.id'));
	 *
	 * @param   mixed  $columns  A string or an array of field names.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function exec($columns);

	/**
	 * Add a table to the FROM clause of the query.
	 *
	 * Note that while an array of tables can be provided, it is recommended you use explicit joins.
	 *
	 * Usage:
	 * $query->select('*')->from('#__a');
	 *
	 * @param   mixed   $tables         A string or array of table names.
	 *                                  This can be a JDatabaseQuery object (or a child of it) when used
	 *                                  as a subquery in FROM clause along with a value for $subQueryAlias.
	 * @param   string  $subQueryAlias  Alias used when $tables is a JDatabaseQuery.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function from($tables, $subQueryAlias = null);

	/**
	 * expression
	 *
	 * @param string $name
	 *
	 * @return  string
	 */
	public function expression($name);

	/**
	 * Add a grouping column to the GROUP clause of the query.
	 *
	 * Usage:
	 * $query->group('id');
	 *
	 * @param   mixed  $columns  A string or array of ordering columns.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function group($columns);

	/**
	 * A conditions to the HAVING clause of the query.
	 *
	 * Usage:
	 * $query->group('id')->having('COUNT(id) > 5');
	 *
	 * @param   mixed   $conditions  A string or array of columns.
	 * @param   mixed  ...$args     Support more arguments to format query.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function having($conditions);

	/**
	 * Add a single condition, or an array of conditions to the HAVING clause and wrap with OR elements.
	 *
	 * Usage:
	 * $query->orHaving(array('a < 5', 'b > 6'));
	 * $query->orHaving('a < 5', 'b > 6');
	 * $query->orHaving(function ($query)
	 * {
	 *     $query->having('a < 5')->having('b > 6');
	 * });
	 *
	 * Result:
	 * HAVING ... AND (a < 5 OR b > 6)
	 *
	 * @param   mixed|callable   $conditions  A string, array of where conditions or callback to support logic.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   3.0
	 */
	public function orHaving($conditions);

	/**
	 * Add an INNER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->innerJoin('b ON b.id = a.id')->innerJoin('c ON c.id = b.id');
	 *
	 * @param array|string $table     The table name with alias.
	 * @param array|string $condition The join condition.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function innerJoin($table, $condition = []);

	/**
	 * Add a table name to the INSERT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->insert('#__a')->set('id = 1');
	 * $query->insert('#__a')->columns('id, title')->values('1,2')->values('3,4');
	 * $query->insert('#__a')->columns('id, title')->values(array('1,2', '3,4'));
	 *
	 * @param   mixed    $table           The name of the table to insert data into.
	 * @param   boolean  $incrementField  The name of the field to auto increment.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function insert($table, $incrementField=false);

	/**
	 * Add a JOIN clause to the query.
	 *
	 * Usage:
	 * $query->join('INNER', 'b ON b.id = a.id);
	 *
	 * @param   string        $type        The type of join. This string is prepended to the JOIN keyword.
	 * @param   string        $table       The table name with alias.
	 * @param   string|array  $conditions  A string or array of conditions.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function join($type, $table, $conditions = []);

	/**
	 * Add a LEFT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->leftJoin('b ON b.id = a.id')->leftJoin('c ON c.id = b.id');
	 *
	 * @param array|string $table     The table name with alias.
	 * @param array|string $condition The join condition.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function leftJoin($table, $condition = []);

	/**
	 * Get the null or zero representation of a timestamp for the database driver.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the nullDate method directly.
	 *
	 * Usage:
	 * $query->where('modified_date <> '.$query->nullDate());
	 *
	 * @param   boolean  $quoted  Optionally wraps the null date in database quotes (true by default).
	 *
	 * @return  string  Null or zero representation of a timestamp.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function nullDate($quoted = true);

	/**
	 * Add a ordering column to the ORDER clause of the query.
	 *
	 * Usage:
	 * $query->order('foo')->order('bar');
	 * $query->order(array('foo','bar'));
	 *
	 * @param   mixed  $columns  A string or array of ordering columns.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function order($columns);

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
	 * @return static Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function limit($limit = null, $offset = null);

	/**
	 * Add an OUTER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->outerJoin('b ON b.id = a.id')->outerJoin('c ON c.id = b.id');
	 *
	 * @param array|string $table     The table name with alias.
	 * @param array|string $condition The join condition.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function outerJoin($table, $condition = []);

	/**
	 * Method to quote and optionally escape a string to database requirements for insertion into the database.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the quote method directly.
	 *
	 * Note that 'q' is an alias for this method as it is in DatabaseDriver.
	 *
	 * Usage:
	 * $query->quote('fulltext');
	 * $query->q('fulltext');
	 * $query->q(array('option', 'fulltext'));
	 *
	 * @param   mixed    $text    A string or an array of strings to quote.
	 * @param   boolean  $escape  True to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function quote($text, $escape = true);

	/**
	 * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
	 * risks and reserved word conflicts.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the quoteName method directly.
	 *
	 * Note that 'qn' is an alias for this method as it is in DatabaseDriver.
	 *
	 * Usage:
	 * $query->quoteName('#__a');
	 * $query->qn('#__a');
	 *
	 * @param   mixed  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in quotes.
	 *                        Each type supports dot-notation name.
	 *
	 * @return  mixed  The quote wrapped name, same type of $name.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	public function quoteName($name);

	/**
	 * Add a RIGHT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->rightJoin('b ON b.id = a.id')->rightJoin('c ON c.id = b.id');
	 *
	 * @param array|string $table     The table name with alias.
	 * @param array|string $condition The join condition.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function rightJoin($table, $condition = []);

	/**
	 * Add a single column, or array of columns to the SELECT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The select method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->select('a.*')->select('b.id');
	 * $query->select(array('a.*', 'b.id'));
	 *
	 * @param   mixed  $columns  A string or an array of field names.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function select($columns);

	/**
	 * Add a single condition string, or an array of strings to the SET clause of the query.
	 *
	 * Usage:
	 * $query->set('a = 1')->set('b = 2');
	 * $query->set(array('a = 1', 'b = 2');
	 *
	 * @param   mixed   $conditions  A string or array of string conditions.
	 * @param   string  $glue        The glue by which to join the condition strings. Defaults to ,.
	 *                               Note that the glue is set on first use and cannot be changed.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function set($conditions, $glue = ',');

	/**
	 * Allows a direct query to be provided to the database driver's setQuery() method, but still allow queries
	 * to have bounded variables.
	 *
	 * Usage:
	 * $query->setQuery('select * from #__users');
	 *
	 * @param   mixed  $sql  A SQL query string or DatabaseQuery object
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function setQuery($sql);

	/**
	 * Add a table name to the UPDATE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->update('#__foo')->set(...);
	 *
	 * @param   string  $table  A table to update.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function update($table);

	/**
	 * Adds a tuple, or array of tuples that would be used as values for an INSERT INTO statement.
	 *
	 * Usage:
	 * $query->values('1,2,3')->values('4,5,6');
	 * $query->values(array('1,2,3', '4,5,6'));
	 *
	 * @param   string  $values  A single tuple, or array of tuples.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function values($values);

	/**
	 * Add a single condition, or an array of conditions to the WHERE clause of the query.
	 *
	 * Usage:
	 * $query->where('a = 1')->where('b = 2');
	 * $query->where(array('a = 1', 'b = 2'));
	 *
	 * @param   mixed  $conditions  A string or array of where conditions.
	 * @param   mixed  ...$args     Support more arguments to format query.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   2.0
	 */
	public function where($conditions);

	/**
	 * Add a single condition, or an array of conditions to the WHERE clause and wrap with OR elements.
	 *
	 * Usage:
	 * $query->orWhere(array('a < 5', 'b > 6'));
	 * $query->orWhere('a < 5', 'b > 6');
	 * $query->orWhere(function ($query)
	 * {
	 *     $query->where('a < 5')->where('b > 6');
	 * });
	 *
	 * Result:
	 * WHERE ... AND (a < 5 OR b > 6)
	 *
	 * @param   mixed|callable   $conditions  A string, array of where conditions or callback to support logic.
	 *
	 * @return static  Returns this object to allow chaining.
	 *
	 * @since   3.0
	 */
	public function orWhere($conditions);

	/**
	 * Add a query to UNION with the current query.
	 * Multiple unions each require separate statements and create an array of unions.
	 *
	 * Usage:
	 * $query->union('SELECT name FROM  #__foo')
	 * $query->union('SELECT name FROM  #__foo','distinct')
	 * $query->union(array('SELECT name FROM  #__foo', 'SELECT name FROM  #__bar'))
	 *
	 * @param   mixed    $query     The Query object or string to union.
	 * @param   boolean  $distinct  True to only return distinct rows from the union.
	 *
	 * @return  mixed    The Query object on success or boolean false on failure.
	 *
	 * @since   2.0
	 */
	public function union($query, $distinct = false);

	/**
	 * Add a query to UNION DISTINCT with the current query. Simply a proxy to Union with the Distinct clause.
	 *
	 * Usage:
	 * $query->unionDistinct('SELECT name FROM  #__foo')
	 *
	 * @param   mixed   $query  The Query object or string to union.
	 *
	 * @return  mixed   The Query object on success or boolean false on failure.
	 *
	 * @since   2.0
	 */
	public function unionDistinct($query);

	/**
	 * Add a query to UNION ALL with the current query.
	 * Multiple unions each require separate statements and create an array of unions.
	 *
	 * Usage:
	 * $query->unionAll('SELECT name FROM  #__foo')
	 * $query->unionAll(array('SELECT name FROM  #__foo','SELECT name FROM  #__bar'))
	 *
	 * @param   mixed  $query  The Query object or string to union.
	 *
	 * @return  mixed  The Query object on success or boolean false on failure.
	 *
	 * @see     union
	 *
	 * @since   2.0
	 */
	public function unionAll($query);

	/**
	 * Find and replace sprintf-like tokens in a format string.
	 * Each token takes one of the following forms:
	 *     %%       - A literal percent character.
	 *     %[t]     - Where [t] is a type specifier.
	 *     %[n]$[x] - Where [n] is an argument specifier and [t] is a type specifier.
	 *
	 * Types:
	 * a - Numeric: Replacement text is coerced to a numeric type but not quoted or escaped.
	 * e - Escape: Replacement text is passed to $this->escape().
	 * E - Escape (extra): Replacement text is passed to $this->escape() with true as the second argument.
	 * n - Name Quote: Replacement text is passed to $this->quoteName().
	 * q - Quote: Replacement text is passed to $this->quote().
	 * Q - Quote (no escape): Replacement text is passed to $this->quote() with false as the second argument.
	 * r - Raw: Replacement text is used as-is. (Be careful)
	 *
	 * Date Types:
	 * - Replacement text automatically quoted (use uppercase for Name Quote).
	 * - Replacement text should be a string in date format or name of a date column.
	 * y/Y - Year
	 * m/M - Month
	 * d/D - Day
	 * h/H - Hour
	 * i/I - Minute
	 * s/S - Second
	 *
	 * Invariable Types:
	 * - Takes no argument.
	 * - Argument index not incremented.
	 * t - Replacement text is the result of $this->currentTimestamp().
	 * z - Replacement text is the result of $this->nullDate(false).
	 * Z - Replacement text is the result of $this->nullDate(true).
	 *
	 * Usage:
	 * $query->format('SELECT %1$n FROM %2$n WHERE %3$n = %4$a', 'foo', '#__foo', 'bar', 1);
	 * Returns: SELECT `foo` FROM `#__foo` WHERE `bar` = 1
	 *
	 * Notes:
	 * The argument specifier is optional but recommended for clarity.
	 * The argument index used for unspecified tokens is incremented only when used.
	 *
	 * @param   string  $format  The formatting string.
	 * @param   mixed   $_       More arguments to format. [optional]
	 *
	 * @return  string  Returns a string produced according to the formatting string.
	 *
	 * @since   2.0
	 */
	public function format($format);
}
