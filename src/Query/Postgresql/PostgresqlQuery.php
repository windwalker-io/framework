<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Postgresql;

use Windwalker\Query\Query;
use Windwalker\Query\QueryElement;

/**
 * Class PostgresqlQuery
 *
 * @since 2.0
 */
class PostgresqlQuery extends Query
{
    /**
     * The database driver name
     *
     * @var    string
     * @since  2.0
     */
    public $name = 'postgresql';

    /**
     * The character(s) used to quote SQL statement names such as table names or field names,
     * etc. The child classes should define this as necessary.  If a single character string the
     * same character is used for both sides of the quoted name, else the first character will be
     * used for the opening quote and the second for the closing quote.
     *
     * @var    string
     * @since  2.0
     */
    protected $nameQuote = '"';

    /**
     * The null or zero representation of a timestamp for the database driver.  This should be
     * defined in child classes to hold the appropriate value for the engine.
     *
     * @var    string
     * @since  2.0
     */
    protected $nullDate = '1970-01-01 00:00:00';

    /**
     * The FOR UPDATE element used in "FOR UPDATE" lock
     *
     * @var    object
     * @since  2.0
     */
    protected $forUpdate = null;

    /**
     * The FOR SHARE element used in "FOR SHARE" lock
     *
     * @var    object
     * @since  2.0
     */
    protected $forShare = null;

    /**
     * The NOWAIT element used in "FOR SHARE" and "FOR UPDATE" lock
     *
     * @var    object
     * @since  2.0
     */
    protected $noWait = null;

    /**
     * The LIMIT element
     *
     * @var    integer
     * @since  2.0
     */
    protected $limit = null;

    /**
     * The OFFSET element
     *
     * @var    integer
     * @since  2.0
     */
    protected $offset = null;

    /**
     * The RETURNING element of INSERT INTO
     *
     * @var    object
     * @since  2.0
     */
    protected $returning = null;

    /**
     * Magic function to convert the query to a string, only for PostgreSQL specific queries
     *
     * @return  string    The completed query.
     *
     * @since   2.0
     */
    public function toString()
    {
        $query = '';

        switch ($this->type) {
            case 'select':
                $query .= (string) $this->select;
                $query .= (string) $this->from;

                if ($this->join) {
                    // Special case for joins
                    foreach ($this->join as $join) {
                        $query .= (string) $join;
                    }
                }

                if ($this->where) {
                    $query .= (string) $this->where;
                }

                if ($this->group) {
                    $query .= (string) $this->group;
                }

                if ($this->having) {
                    $query .= (string) $this->having;
                }

                if ($this->order) {
                    $query .= (string) $this->order;
                }

                if ($this->forUpdate) {
                    $query .= (string) $this->forUpdate;
                } else {
                    if ($this->forShare) {
                        $query .= (string) $this->forShare;
                    }
                }

                if ($this->noWait) {
                    $query .= (string) $this->noWait;
                }

                break;

            case 'update':
                $query .= (string) $this->update;
                $query .= (string) $this->set;

                if ($this->join) {
                    $onWord = ' ON ';

                    // Workaround for special case of JOIN with UPDATE
                    foreach ($this->join as $join) {
                        $joinElem = $join->getElements();

                        $joinArray = explode($onWord, $joinElem[0]);

                        $this->from($joinArray[0]);
                        $this->where($joinArray[1]);
                    }

                    $query .= (string) $this->from;
                }

                if ($this->where) {
                    $query .= (string) $this->where;
                }

                break;

            case 'insert':
                $query .= (string) $this->insert;

                if ($this->values) {
                    if ($this->columns) {
                        $query .= (string) $this->columns;
                    }

                    $elements = $this->values->getElements();

                    if (!($elements[0] instanceof $this)) {
                        $query .= ' VALUES ';
                    }

                    $query .= (string) $this->values;

                    if ($this->returning) {
                        $query .= (string) $this->returning;
                    }
                }

                break;

            default:
                $query = parent::toString();
                break;
        }

        $query = $this->processLimit($query, $this->limit, $this->offset);

        if ($this->suffix) {
            $query .= ' ' . (string) $this->suffix;
        }

        if ($this->type === 'select' && $this->alias !== null) {
            $query = sprintf('(%s) AS %s', $query, $this->alias);
        }

        return $query;
    }

    /**
     * Clear data from the query or a specific clause of the query.
     *
     * @param   string $clause Optionally, the name of the clause to clear, or nothing to clear the whole query.
     *
     * @return  PostgresqlQuery  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function clear($clause = null)
    {
        switch ($clause) {
            case 'limit':
                $this->limit = null;
                break;

            case 'offset':
                $this->offset = null;
                break;

            case 'forUpdate':
                $this->forUpdate = null;
                break;

            case 'forShare':
                $this->forShare = null;
                break;

            case 'noWait':
                $this->noWait = null;
                break;

            case 'returning':
                $this->returning = null;
                break;

            case 'select':
            case 'update':
            case 'delete':
            case 'insert':
            case 'from':
            case 'join':
            case 'set':
            case 'where':
            case 'group':
            case 'having':
            case 'order':
            case 'columns':
            case 'values':
            case 'suffix':
            case 'alias':
                parent::clear($clause);
                break;

            default:
                $this->type = null;
                $this->limit = null;
                $this->offset = null;
                $this->forUpdate = null;
                $this->forShare = null;
                $this->noWait = null;
                $this->returning = null;
                parent::clear($clause);
                break;
        }

        return $this;
    }

    /**
     * Sets the FOR UPDATE lock on select's output row
     *
     * @param   string $table_name The table to lock
     * @param   string $glue       The glue by which to join the conditions. Defaults to ',' .
     *
     * @return  PostgresqlQuery  FOR UPDATE query element
     *
     * @since   2.0
     */
    public function forUpdate($table_name = null, $glue = ',')
    {
        if (is_null($this->forUpdate)) {
            $glue = strtoupper($glue);
            $this->forUpdate = new QueryElement('FOR UPDATE', $table_name ? 'OF ' . $table_name : null, "$glue ");
        } else {
            $this->forUpdate->append($table_name);
        }

        return $this;
    }

    /**
     * Sets the FOR SHARE lock on select's output row
     *
     * @param   string $table_name The table to lock
     * @param   string $glue       The glue by which to join the conditions. Defaults to ',' .
     *
     * @return  PostgresqlQuery  FOR SHARE query element
     *
     * @since   2.0
     */
    public function forShare($table_name = null, $glue = ',')
    {
        if (is_null($this->forShare)) {
            $glue = strtoupper($glue);
            $this->forShare = new QueryElement('FOR SHARE', $table_name ? 'OF ' . $table_name : null, "$glue ");
        } else {
            $this->forShare->append($table_name);
        }

        return $this;
    }

    /**
     * Sets the NOWAIT lock on select's output row
     *
     * @return  PostgresqlQuery  NOWAIT query element
     *
     * @since   2.0
     */
    public function noWait()
    {
        $this->type = 'noWait';

        if (is_null($this->noWait)) {
            $this->noWait = new QueryElement('NOWAIT', null);
        }

        return $this;
    }

    /**
     * Set the LIMIT clause to the query
     *
     * @param   integer $limit  Number of rows to return
     * @param   integer $offset The offset for the result set
     *
     * @return PostgresqlQuery Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function limit($limit = null, $offset = null)
    {
        if (is_null($this->limit)) {
            $this->limit = $limit;
        }

        $this->offset($offset);

        return $this;
    }

    /**
     * Set the OFFSET clause to the query
     *
     * @param   integer $offset An integer for skipping rows
     *
     * @return  PostgresqlQuery  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function offset($offset = 0)
    {
        if (is_null($this->offset)) {
            $this->offset = $offset;
        }

        return $this;
    }

    /**
     * Add the RETURNING element to INSERT INTO statement.
     *
     * @param   mixed $pkCol The name of the primary key column.
     *
     * @return  PostgresqlQuery  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function returning($pkCol)
    {
        if (is_null($this->returning)) {
            $this->returning = new QueryElement('RETURNING', $pkCol);
        }

        return $this;
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
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit;
        }

        if ($offset > 0) {
            $query .= ' OFFSET ' . $offset;
        }

        return $query;
    }
}
