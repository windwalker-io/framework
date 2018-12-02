<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query\Mysql;

use Windwalker\Query\AbstractQueryGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\QueryElement;

/**
 * Class MysqlQueryGrammar
 *
 * @since 2.0
 */
class MysqlGrammar extends AbstractQueryGrammar
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
     * @param array $where
     *
     * @return  string
     */
    public static function listDatabases($where = null)
    {
        $where = $where ? new QueryElement('WHERE', $where, 'AND') : null;

        return 'SHOW DATABASES ' . $where;
    }

    /**
     * createDatabase
     *
     * @param string $name
     * @param bool   $isNotExists
     * @param string $charset
     * @param string $collate
     *
     * @return  string
     */
    public static function createDatabase($name, $isNotExists = false, $charset = null, $collate = null)
    {
        $query = static::getQuery();

        return static::build(
            'CREATE DATABASE',
            $isNotExists ? 'IF NOT EXISTS' : null,
            $query->quoteName($name),
            $charset ? 'CHARACTER SET=' . $query->quote($charset) : null,
            $collate ? 'COLLATE=' . $query->quote($collate) : null
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
     * @param string       $table
     * @param bool         $full
     * @param string|array $where
     *
     * @return  string
     */
    public static function showTableColumns($table, $full = false, $where = null)
    {
        $query = static::getQuery();

        return static::build(
            'SHOW',
            $full ? 'FULL' : false,
            'COLUMNS FROM',
            $query->quoteName($table),
            $where ? new QueryElement('WHERE', $where, 'AND') : null
        );
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
        $query = static::getQuery();

        return static::build(
            'SHOW',
            'TABLE STATUS FROM',
            $query->quoteName($dbname),
            $where ? new QueryElement('WHERE', $where, 'AND') : null
        );
    }

    /**
     * createTable
     *
     * @param string       $name
     * @param array        $columns
     * @param array|string $pks
     * @param array        $keys
     * @param null         $autoIncrement
     * @param bool         $ifNotExists
     * @param string       $engine
     * @param string       $defaultCharset
     * @param string       $collate
     *
     * @return  string
     */
    public static function createTable(
        $name,
        $columns,
        $pks = [],
        $keys = [],
        $autoIncrement = null,
        $ifNotExists = true,
        $engine = 'InnoDB',
        $defaultCharset = 'utf8mb4',
        $collate = 'utf8mb4_unicode_ci'
    ) {
        $query = static::getQuery();
        $cols = [];
        $engine = $engine ?: 'InnoDB';

        foreach ($columns as $cName => $details) {
            $details = (array) $details;

            array_unshift($details, $query->quoteName($cName));

            $cols[] = call_user_func_array([get_called_class(), 'build'], $details);
        }

        if (!is_array($keys)) {
            throw new \InvalidArgumentException('Keys should be an array');
        }

        if ($pks) {
            $pks = [
                'type' => 'PRIMARY KEY',
                'columns' => (array) $pks,
            ];

            array_unshift($keys, $pks);
        }

        foreach ($keys as $key) {
            $define = [
                'type' => 'KEY',
                'name' => null,
                'columns' => [],
                'comment' => '',
            ];

            if (!is_array($key)) {
                throw new \InvalidArgumentException('Every key data should be an array with "type", "name", "columns"');
            }

            $define = array_merge($define, $key);

            $cols[] = strtoupper($define['type'])
                . ' ' . static::buildIndexDeclare($define['name'], $define['columns']);
        }

        $cols = "(\n" . implode(",\n", $cols) . "\n)";

        return static::build(
            'CREATE TABLE',
            $ifNotExists ? 'IF NOT EXISTS' : null,
            $query->quoteName($name),
            $cols,
            'ENGINE=' . $engine,
            $autoIncrement ? 'AUTO_INCREMENT=' . $autoIncrement : null,
            $defaultCharset ? 'DEFAULT CHARSET=' . $defaultCharset : null,
            $collate ? 'COLLATE=' . $collate : null
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
     * alterColumn
     *
     * @param string $operation
     * @param string $table
     * @param string $column
     * @param string $type
     * @param bool   $signed
     * @param bool   $allowNull
     * @param null   $default
     * @param null   $position
     * @param string $comment
     *
     * @return  string
     */
    public static function alterColumn(
        $operation,
        $table,
        $column,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = null,
        $position = null,
        $comment = ''
    ) {
        $query = static::getQuery();

        $column = $query->quoteName((array) $column);

        return static::build(
            'ALTER TABLE',
            $query->quoteName($table),
            $operation,
            implode(' ', $column),
            $type ?: 'text',
            $signed ? null : 'UNSIGNED',
            $allowNull ? null : 'NOT NULL',
            $default !== false ? 'DEFAULT ' . static::getQuery()->validValue($default) : null,
            $comment ? 'COMMENT ' . $query->quote($comment) : null,
            static::handleColumnPosition($position)
        );
    }

    /**
     * Add column
     *
     * @param string $table
     * @param string $column
     * @param string $type
     * @param bool   $signed
     * @param bool   $allowNull
     * @param string $default
     * @param string $position
     * @param string $comment
     *
     * @return  string
     */
    public static function addColumn(
        $table,
        $column,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = null,
        $position = null,
        $comment = ''
    ) {
        return static::alterColumn('ADD', $table, $column, $type, $signed, $allowNull, $default, $position, $comment);
    }

    /**
     * changeColumn
     *
     * @param string $table
     * @param string $oldColumn
     * @param string $newColumn
     * @param string $type
     * @param bool   $signed
     * @param bool   $allowNull
     * @param null   $default
     * @param string $position
     * @param string $comment
     *
     * @return  string
     */
    public static function changeColumn(
        $table,
        $oldColumn,
        $newColumn,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = null,
        $position = null,
        $comment = ''
    ) {
        $column = [$oldColumn, $newColumn];

        return static::alterColumn(
            'CHANGE',
            $table,
            $column,
            $type,
            $signed,
            $allowNull,
            $default,
            $position,
            $comment
        );
    }

    /**
     * modifyColumn
     *
     * @param string $table
     * @param string $column
     * @param string $type
     * @param bool   $signed
     * @param bool   $allowNull
     * @param null   $default
     * @param string $position
     * @param string $comment
     *
     * @return  string
     */
    public static function modifyColumn(
        $table,
        $column,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = null,
        $position = null,
        $comment = ''
    ) {
        return static::alterColumn(
            'MODIFY',
            $table,
            $column,
            $type,
            $signed,
            $allowNull,
            $default,
            $position,
            $comment
        );
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
     * @param string       $comment
     *
     * @return string
     */
    public static function addIndex($table, $type, $columns, $name, $comment = null)
    {
        $query = static::getQuery();
        $cols = static::buildIndexDeclare($name, $columns);

        $comment = $comment ? 'COMMENT ' . $query->quote($comment) : '';

        return static::build(
            'ALTER TABLE',
            $query->quoteName($table),
            'ADD',
            strtoupper($type),
            $cols,
            $comment
        );
    }

    /**
     * buildIndexDeclare
     *
     * @param string $name
     * @param array  $columns
     * @param string $table
     *
     * @return string
     */
    public static function buildIndexDeclare($name, $columns, $table = null)
    {
        $query = static::getQuery();
        $cols = [];

        foreach ((array) $columns as $key => $val) {
            if (is_numeric($key)) {
                $vals = explode('(', $val);
                $key = $query->quoteName($vals[0]);

                if (isset($vals[1])) {
                    $key .= '(' . trim($vals[1], '()') . ')';
                }

                $cols[] = $key;
            } else {
                if (!is_numeric($val)) {
                    $string = is_string($val) ? ' ' . $query->quote($val) : '';

                    throw new \InvalidArgumentException(
                        sprintf(
                            'Index length should be number, (%s)%s given.',
                            gettype($val),
                            $string
                        )
                    );
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
     * @param string $table
     * @param string $name
     *
     * @return  string
     */
    public static function dropIndex($table, $name)
    {
        $query = static::getQuery();

        return static::build(
            'DROP INDEX',
            $query->quoteName($name),
            'ON',
            $query->quoteName($table)
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

        return implode(' ', $args);
    }

    /**
     * handleColumnPosition
     *
     * @param string $position
     *
     * @return  string
     */
    protected static function handleColumnPosition($position)
    {
        $query = static::getQuery();

        if (!$position) {
            return null;
        }

        $posColumn = '';

        $position = trim($position);

        if (strpos(strtoupper($position), 'AFTER') !== false) {
            list($position, $posColumn) = explode(' ', $position, 2);

            $posColumn = $query->quoteName($posColumn);
        }

        return $position . ' ' . $posColumn;
    }

    /**
     * replace
     *
     * @param string $name
     * @param array  $columns
     * @param array  $values
     *
     * @return  string
     */
    public static function replace($name, $columns = [], $values = [])
    {
        $query = new MysqlQuery();

        $query = (string) $query->insert($query->quoteName($name))
            ->columns($columns)
            ->values($values);

        $query = substr(trim($query), 6);

        return 'REPLACE' . $query;
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
            static::$query = new MysqlQuery();
        }

        return static::$query;
    }
}
