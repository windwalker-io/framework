<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Postgresql;

use Windwalker\Query\AbstractQueryGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\QueryElement;

/**
 * Class PostgresqlQueryGrammar
 *
 * @since 2.0
 */
class PostgresqlGrammar extends AbstractQueryGrammar
{
    const PRIMARY = 'PRIMARY KEY';
    const INDEX = 'INDEX';
    const UNIQUE = 'UNIQUE';
    const SPATIAL = 'SPATIAL';
    const FULLTEXT = 'UNIQUE';
    const FOREIGN = 'FOREIGN KEY';

    /**
     * Property query.
     *
     * @var  Query
     */
    public static $query = null;

    /**
     * showDatabases
     *
     * @param array|string $where
     *
     * @return  string
     */
    public static function listDatabases($where = null)
    {
        $where   = (array)$where;
        $where[] = 'datistemplate = false';
        $where   = new QueryElement('WHERE', $where, ' AND ');

        return 'SELECT datname FROM pg_database ' . $where . ';';
    }

    /**
     * createDatabase
     *
     * @param string $name
     * @param string $encoding
     * @param string $owner
     *
     * @return  string
     */
    public static function createDatabase($name, $encoding = null, $owner = null)
    {
        $query = static::getQuery();

        return static::build(
            'CREATE DATABASE',
            $query->quoteName($name),
            $encoding ? 'ENCODING ' . $query->quote($encoding) : null,
            $owner ? 'OWNER ' . $query->quoteName($owner) : null
        );
    }

    /**
     * dropTable
     *
     * @param string $db
     * @param bool   $ifExist
     *
     * @return  string
     */
    public static function dropDatabase($db, $ifExist = false)
    {
        $query = static::getQuery();

        return static::build(
            'DROP DATABASE',
            $ifExist ? 'IF EXISTS' : null,
            $query->quoteName($db)
        );
    }

    /**
     * showTableColumn
     *
     * @param string $table
     *
     * @return  string
     */
    public static function showTableColumns($table)
    {
        $query = static::getQuery(true);

        // Field
        $query->select('attr.attname AS "column_name"')
            ->from('pg_catalog.pg_attribute AS attr')
            ->leftJoin('pg_catalog.pg_class AS class', 'class.oid = attr.attrelid');

        // Type
        $query->select('pg_catalog.format_type(attr.atttypid, attr.atttypmod) AS "column_type"')
            ->leftJoin('pg_catalog.pg_type AS typ', 'typ.oid = attr.atttypid');

        // Is Null
        $query->select('CASE WHEN attr.attnotnull IS TRUE THEN \'NO\' ELSE \'YES\' END AS "Null"');

        // Default
        $query->select('attrdef.adsrc AS "Default"')
            ->leftJoin('pg_catalog.pg_attrdef AS attrdef',
                'attr.attrelid = attrdef.adrelid AND attr.attnum = attrdef.adnum');

        // Comment
        $query->select('pg_catalog.col_description(attr.attrelid, attr.attnum) AS "Comment"');

        // General
        $query->where('attr.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname=' . $query->quote($table) . '
	AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE
	nspname = \'public\'))')
            ->where('attr.attnum > 0 AND NOT attr.attisdropped')
            ->order('attr.attnum');

        return (string)$query;
    }

    /**
     * showDbTables
     *
     * @param string $dbname
     * @param string $where
     *
     * @return  string
     */
    public static function showDbTables($dbname, $where = null)
    {
        $query = static::getQuery(true);

        $query->select('table_name AS "Name"')
            ->from('information_schema.tables')
            ->where('table_type=' . $query->quote('BASE TABLE'))
            ->where('table_schema NOT IN (' . $query->quote('pg_catalog') . ', ' . $query->quote('information_schema') . ')')
            ->order('table_name ASC');

        if ($where) {
            $query->where($where);
        }

        return (string)$query;
    }

    /**
     * createTable
     *
     * @param string       $name
     * @param array        $columns
     * @param array|string $pks
     * @param array        $keys
     * @param string       $inherits
     * @param bool         $ifNotExists
     * @param string       $tablespace
     *
     * @return string
     */
    public static function createTable(
        $name,
        $columns,
        $pks = [],
        $keys = [],
        $inherits = null,
        $ifNotExists = true,
        $tablespace = null
    ) {
        $query = static::getQuery();
        $cols  = [];

        foreach ($columns as $cName => $details) {
            $details = (array)$details;

            array_unshift($details, $query->quoteName($cName));

            $cols[] = call_user_func_array([get_called_class(), 'build'], $details);
        }

        if (!is_array($keys)) {
            throw new \InvalidArgumentException('Keys should be an array');
        }

        if ($pks) {
            $cols[] = 'PRIMARY KEY ' . static::buildIndexDeclare(null, (array)$pks, null);
        }

        $indexes = [];

        foreach ($keys as $key) {
            $define = [
                'type' => 'INDEX',
                'name' => null,
                'columns' => [],
                'comment' => '',
            ];

            if (!is_array($key)) {
                throw new \InvalidArgumentException('Every key data should be an array with "type", "name", "columns"');
            }

            $define = array_merge($define, $key);

            $indexes[] = 'CREATE ' . $define['type'] . ' ' . static::buildIndexDeclare($define['name'],
                    $define['columns'], $name);
        }

        $indexes = implode(";\n", $indexes);

        $cols = "(\n" . implode(",\n", $cols) . "\n)";

        return static::build(
            'CREATE TABLE',
            $ifNotExists ? 'IF NOT EXISTS' : null,
            $query->quoteName($name),
            $cols,
            $inherits ? 'INHERITS (' . implode(',', $query->quoteName((array)$inherits)) . ')' : null,
            $tablespace ? 'TABLESPACE ' . $query->quoteName($tablespace) : null,
            ";\n",
            $indexes ? $indexes : null
        );
    }

    /**
     * dropTable
     *
     * @param string $table
     * @param bool   $ifExists
     * @param string $option
     *
     * @return  string
     */
    public static function dropTable($table, $ifExists = false, $option = '')
    {
        $query = static::getQuery();

        return static::build(
            'DROP TABLE',
            $ifExists ? 'IF EXISTS' : null,
            $query->quoteName($table),
            $option
        );
    }

    /**
     * comment
     *
     * @param string $object
     * @param string $table
     * @param string $column
     * @param string $comment
     *
     * @return string
     */
    public static function comment($object = 'COLUMN', $table, $column, $comment)
    {
        $query = static::getQuery();

        return static::build(
            'COMMENT ON ' . $object,
            $query->quoteName($table) . '.' . $query->quoteName($column),
            'IS',
            $query->quote($comment)
        );
    }

    /**
     * alterColumn
     *
     * @param string $operation
     * @param string $table
     * @param string $column
     * @param string $type
     * @param bool   $notNull
     * @param null   $default
     *
     * @return  string
     */
    public static function alterColumn($operation, $table, $column, $type = null, $notNull = false, $default = null)
    {
        $query = static::getQuery();

        $column = $query->quoteName((array)$column);

        return static::build(
            'ALTER TABLE',
            $query->quoteName($table),
            $operation,
            implode(' TO ', $column),
            $type,
            $notNull ? 'NOT NULL' : null,
            !is_null($default) ? 'SET DEFAULT ' . $query->quote($default) : null
        );
    }

    /**
     * Add column
     *
     * @param string $table
     * @param string $column
     * @param string $type
     * @param bool   $allowNull
     * @param string $default
     *
     * @return  string
     */
    public static function addColumn($table, $column, $type = 'text', $allowNull = false, $default = null)
    {
        $query = static::getQuery();

        return static::build(
            'ALTER TABLE',
            $query->quoteName($table),
            'ADD',
            $query->quoteName($column),
            $type,
            $allowNull ? null : 'NOT NULL',
            $default ? 'DEFAULT' . $query->quote($default) : null
        );
    }

    /**
     * changeColumn
     *
     * @param string $table
     * @param string $oldColumn
     * @param string $newColumn
     *
     * @return  string
     */
    public static function renameColumn($table, $oldColumn, $newColumn)
    {
        $column = [$oldColumn, $newColumn];

        return static::alterColumn('RENAME', $table, $column);
    }

    /**
     * dropColumn
     *
     * @param string $table
     * @param string $column
     *
     * @return  string
     */
    public static function dropColumn($table, $column)
    {
        $query = static::getQuery();

        return static::build(
            'ALTER TABLE',
            $query->quoteName($table),
            'DROP',
            $query->quoteName($column)
        );
    }

    /**
     * addIndex
     *
     * @param string       $table
     * @param string       $type
     * @param string|array $columns
     * @param string       $name
     *
     * @return string
     */
    public static function addIndex($table, $type, $columns, $name)
    {
        $cols = static::buildIndexDeclare($name, $columns, $table);

        return static::build(
            'CREATE',
            strtoupper($type),
            $cols
        );
    }

    /**
     * buildIndexDeclare
     *
     * @param string $name
     * @param array  $columns
     *
     * @param        $table
     *
     * @return string
     */
    public static function buildIndexDeclare($name, $columns, $table = null)
    {
        $query = static::getQuery();
        $cols  = [];

        foreach ((array)$columns as $key => $val) {
            if (is_numeric($key)) {
                $cols[] = $query->quoteName($val);
            } else {
                if (!is_numeric($val)) {
                    $string = is_string($val) ? ' ' . $query->quote($val) : '';

                    throw new \InvalidArgumentException(sprintf('Index length should be number, (%s)%s given.',
                        gettype($val), $string));
                }

                $cols[] = $query->quoteName($key) . '(' . $val . ')';
            }
        }

        $cols = '(' . implode(', ', $cols) . ')';

        return static::build(
            $name ? $query->quoteName($name) : null,
            $table ? 'ON ' . $query->quoteName($table) : null,
            $cols
        );
    }

    /**
     * dropIndex
     *
     * @param string $name
     * @param bool   $ifExists
     * @param bool   $concurrently
     *
     * @return string
     */
    public static function dropIndex($name, $ifExists = false, $concurrently = false)
    {
        $query = static::getQuery();

        return static::build(
            'DROP INDEX',
            $concurrently ? 'CONCURRENTLY' : null,
            $ifExists ? 'IF EXISTS' : null,
            $query->quoteName($name)
        );
    }

    /**
     * dropConstraint
     *
     * @param string $table
     * @param string $name
     * @param bool   $ifExists
     * @param string $action
     *
     * @return  string
     */
    public static function dropConstraint($table, $name, $ifExists = false, $action = null)
    {
        $query = static::getQuery();

        return static::build(
            'ALTER TABLE',
            $query->quoteName($table),
            'DROP CONSTRAINT',
            $ifExists ? 'IF EXISTS' : null,
            $query->quoteName($name),
            $action
        );
    }

    /**
     * build
     *
     * @return  string
     */
    public static function build()
    {
        $args = func_get_args();

        $sql = [];

        foreach ($args as $arg) {
            if ($arg === '' || $arg === null || $arg === false) {
                continue;
            }

            $sql[] = $arg;
        }

        return implode(' ', $sql);
    }

    /**
     * getQuery
     *
     * @param bool $new
     *
     * @return  Query
     */
    public static function getQuery($new = false)
    {
        if (!static::$query || $new) {
            static::$query = new PostgresqlQuery;
        }

        return static::$query;
    }

    /**
     * changeColumn
     *
     * @param string $table
     * @param string $oldColumn
     * @param string $newColumn
     * @param string $type
     *
     * @return  string
     */
    public static function changeColumn($table, $oldColumn, $newColumn, $type = 'text')
    {
        // No use now
    }
}

