<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Query;

use Windwalker\Compare\Compare;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Query\Query;
use Windwalker\Query\QueryElement;
use Windwalker\Query\QueryInterface;

/**
 * Class QueryHelper
 */
class QueryHelper
{
    /**
     * Property db.
     *
     * @var  AbstractDatabaseDriver
     */
    protected $db = null;

    /**
     * Property tables.
     *
     * @var  array
     */
    protected $tables = [];

    /**
     * Constructor.
     *
     * @param AbstractDatabaseDriver $db
     */
    public function __construct(AbstractDatabaseDriver $db = null)
    {
        $this->db = $db ?: $this->getDb();
    }

    /**
     * addTable
     *
     * @param string  $alias
     * @param string  $table
     * @param mixed   $condition
     * @param string  $joinType
     * @param boolean $prefix
     *
     * @return  QueryHelper
     */
    public function addTable($alias, $table, $condition = null, $joinType = 'LEFT', $prefix = null)
    {
        $tableStorage = [];

        $tableStorage['name'] = $table;
        $tableStorage['join'] = strtoupper($joinType);

        $condition = trim((string) new QueryElement('', (array) $condition, ' AND '));

        if (!$condition) {
            $tableStorage['join'] = 'FROM';
        }

        // Remove too many spaces
        $condition = preg_replace('/\s(?=\s)/', '', $condition);

        $tableStorage['condition'] = $condition;
        $tableStorage['prefix'] = $prefix;

        $this->tables[$alias] = $tableStorage;

        return $this;
    }

    /**
     * removeTable
     *
     * @param string $alias
     *
     * @return  $this
     */
    public function removeTable($alias)
    {
        if (!empty($this->tables[$alias])) {
            unset($this->tables[$alias]);
        }

        return $this;
    }

    /**
     * getFilterFields
     *
     * @return  array
     */
    public function getSelectFields()
    {
        $fields = [];

        $i = 0;

        foreach ($this->tables as $alias => $table) {
            $columns = $this->db->getTable($table['name'])->getColumns();

            foreach ($columns as $column) {
                $prefix = $table['prefix'];

                if ($i === 0) {
                    $prefix = $prefix === null ? false : true;
                } else {
                    $prefix = $prefix === null ? true : false;
                }

                if ($prefix === true) {
                    $fields[] = $this->db->quoteName("{$alias}.{$column} AS {$alias}_{$column}");
                } else {
                    $fields[] = $this->db->quoteName("{$alias}.{$column} AS {$column}");
                }
            }

            $i++;
        }

        return $fields;
    }

    /**
     * registerQueryTables
     *
     * @param QueryInterface $query
     *
     * @return  QueryInterface
     */
    public function registerQueryTables(QueryInterface $query)
    {
        foreach ($this->tables as $alias => $table) {
            if ($table['join'] === 'FROM') {
                $query->from($query->quoteName($table['name']) . ' AS ' . $query->quoteName($alias));
            } else {
                $query->join(
                    $table['join'],
                    $query->quoteName($table['name']) . ' AS ' . $query->quoteName($alias),
                    $table['condition']
                );
            }
        }

        return $query;
    }

    /**
     * buildConditions
     *
     * @param QueryInterface $query
     * @param array          $conditions
     * @param bool           $allowNulls
     *
     * @return Query
     */
    public static function buildWheres(QueryInterface $query, array $conditions, $allowNulls = true)
    {
        foreach ($conditions as $key => $value) {
            // NULL
            if ($value === null && $allowNulls) {
                $query->where($query->format('%n IS NULL', $key));
            } elseif ($value instanceof Compare) {
                // If using Compare class, we convert it to string.
                $query->where((string) static::buildCompare($key, $value, $query));
            } elseif (is_numeric($key)) {
                // If key is numeric, just send value to query where.
                $query->where($value);
            } elseif (is_array($value) || is_object($value)) {
                // If is array or object, we use "IN" condition.
                if ($value instanceof \Traversable) {
                    $value = iterator_to_array($value);
                } elseif (is_object($value)) {
                    $value = get_object_vars($value);
                }

                $value = array_map([$query, 'quote'], $value);

                $query->where($query->quoteName($key) . new QueryElement('IN ()', $value, ','));
            } else {
                // Otherwise, we use equal condition.
                $query->where($query->format('%n = %q', $key, $value));
            }
        }

        return $query;
    }

    /**
     * buildCompare
     *
     * @param string|int     $key
     * @param Compare        $value
     * @param QueryInterface $query
     *
     * @return  string
     */
    public static function buildCompare($key, Compare $value, QueryInterface $query = null)
    {
        $query = $query ?: DatabaseFactory::getDbo()->getQuery(true);

        if (!is_numeric($key)) {
            $value->setCompare1($key);
        }

        $value->setHandler(
            function ($compare1, $compare2, $operator) use ($query) {
                return $query->format('%n ' . $operator . ' %q', $compare1, $compare2);
            }
        );

        return (string) $value;
    }

    /**
     * buildValueAssign
     *
     * @param string              $key
     * @param mixed               $value
     * @param QueryInterface|null $query
     *
     * @return  string
     */
    public static function buildValueAssign($key, $value, QueryInterface $query = null)
    {
        $query = $query ?: DatabaseFactory::getDbo()->getQuery(true);

        if ($value === null) {
            return $query->format('%n = NULL', $key);
        }

        if ($value instanceof \DateTimeInterface) {
            return $query->format('%n = %q', $key, $value->format($query->getDateFormat()));
        }

        return $query->format('%n = %q', $key, $value);
    }

    /**
     * getDb
     *
     * @return  AbstractDatabaseDriver
     */
    public function getDb()
    {
        if (!$this->db) {
            $this->db = DatabaseFactory::getDbo();
        }

        return $this->db;
    }

    /**
     * setDb
     *
     * @param   AbstractDatabaseDriver $db
     *
     * @return  QueryHelper  Return self to support chaining.
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Method to get property Tables
     *
     * @return  array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Method to set property tables
     *
     * @param   array $tables
     *
     * @return  static  Return self to support chaining.
     */
    public function setTables(array $tables)
    {
        $this->tables = $tables;

        return $this;
    }
}
