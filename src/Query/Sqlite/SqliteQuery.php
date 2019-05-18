<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query\Sqlite;

use Windwalker\Query\Query;

/**
 * Class SqliteQuery
 *
 * @since 2.0
 */
class SqliteQuery extends Query
{
    /**
     * The name of the database driver.
     *
     * @var    string
     * @since  2.0
     */
    public $name = 'sqlite';

    /**
     * The character(s) used to quote SQL statement names such as table names or field names,
     * etc. The child classes should define this as necessary.  If a single character string the
     * same character is used for both sides of the quoted name, else the first character will be
     * used for the opening quote and the second for the closing quote.
     *
     * @var    string
     * @since  2.0
     */
    protected $nameQuote = '`';

    /**
     * Holds key / value pair of bound objects.
     *
     * @var    mixed
     * @since  2.0
     */
    protected $bounded = [];

    /**
     * Method to escape a string for usage in an SQLite statement.
     *
     * Note: Using query objects with bound variables is preferable to the below.
     *
     * @param   string  $text  The string to be escaped.
     * @param   boolean $extra Unused optional parameter to provide extra escaping.
     *
     * @return  string  The escaped string.
     *
     * @since   2.0
     */
    public function escape($text, $extra = false)
    {
        if (is_int($text) || is_float($text)) {
            return $text;
        }

        if (!class_exists('SQLite3') || !is_callable(['SQLite3', 'escapeString'])) {
            return $this->escapeWithNoConnection($text);
        }

        return \SQLite3::escapeString($text);
    }

    /**
     * Clear data from the query or a specific clause of the query.
     *
     * @param   string $clause Optionally, the name of the clause to clear, or nothing to clear the whole query.
     *
     * @return  static  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function clear($clause = null)
    {
        switch ($clause) {
            case null:
                $this->bounded = [];
                break;
        }

        return parent::clear($clause);
    }
}
