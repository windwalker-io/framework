<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query\Sqlsrv;

use Windwalker\Query\Query;

/**
 * Class SqlservQuery
 *
 * @since 2.0
 */
class SqlsrvQuery extends Query
{
    /**
     * The name of the database driver.
     *
     * @var    string
     * @since  2.0
     */
    public $name = 'sqlsrv';

    /**
     * The character(s) used to quote SQL statement names such as table names or field names,
     * etc.  The child classes should define this as necessary.  If a single character string the
     * same character is used for both sides of the quoted name, else the first character will be
     * used for the opening quote and the second for the closing quote.
     *
     * @var    string
     * @since  2.0
     */
    protected $nameQuote = '[]';

    /**
     * The null or zero representation of a timestamp for the database driver.  This should be
     * defined in child classes to hold the appropriate value for the engine.
     *
     * @var    string
     * @since  2.0
     */
    protected $nullDate = '1900-01-01 00:00:00';

    /**
     * Method to escape a string for usage in an SQL statement.
     *
     * The escaping for MSSQL isn't handled in the driver though that would be nice.  Because of this we need
     * to handle the escaping ourselves.
     *
     * @param   string  $text  The string to be escaped.
     * @param   boolean $extra Optional parameter to provide extra escaping.
     *
     * @return  string  The escaped string.
     *
     * @since   2.0
     */
    public function escape($text, $extra = false)
    {
        $result = addslashes($text);
        $result = str_replace("\'", "''", $result);
        $result = str_replace('\"', '"', $result);
        $result = str_replace('\/', '/', $result);

        if ($extra) {
            // We need the below str_replace since the search in sql server doesn't recognize _ character.
            $result = str_replace('_', '[_]', $result);
        }

        return $result;
    }

    /**
     * Magic function to convert the query to a string.
     *
     * @return  string    The completed query.
     *
     * @since   2.0
     */
    public function __toString()
    {
        $query = '';

        switch ($this->type) {
            case 'insert':
                $query .= (string) $this->insert;

                // Set method
                if ($this->set) {
                    $query .= (string) $this->set;
                } elseif ($this->values) {
                    // Columns-Values method
                    if ($this->columns) {
                        $query .= (string) $this->columns;
                    }

                    $elements = $this->insert->getElements();
                    $tableName = array_shift($elements);

                    $query .= ' VALUES ';
                    $query .= (string) $this->values;

                    if ($this->autoIncrementField) {
                        $query = 'SET IDENTITY_INSERT ' . $tableName . ' ON;' . $query
                            . 'SET IDENTITY_INSERT ' . $tableName . ' OFF;';
                    }

                    if ($this->where) {
                        $query .= (string) $this->where;
                    }
                }

                break;

            default:
                $query = parent::__toString();
                break;
        }

        return $query;
    }

    /**
     * Method to modify a query already in string format with the needed
     * additions to make the query limited to a particular number of
     * results, or start at a particular offset.
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
        if ($limit) {
            $total = $offset + $limit;

            $position = stripos((string) $query, 'SELECT');
            $distinct = stripos((string) $query, 'SELECT DISTINCT');

            if ($position === $distinct) {
                $query = substr_replace((string) $query, 'SELECT DISTINCT TOP ' . (int) $total, $position, 15);
            } else {
                $query = substr_replace((string) $query, 'SELECT TOP ' . (int) $total, $position, 6);
            }
        }

        if (!$offset) {
            return (string) $query;
        }

        return PHP_EOL
            . 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM ('
            . $query
            . PHP_EOL . ') AS A) AS A WHERE RowNumber > ' . (int) $offset;
    }
}
