<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Mysql\MysqlGrammar;

/**
 * Class MysqlTable
 *
 * @since 2.0
 */
class MysqlTable extends AbstractTable
{
    /**
     * create
     *
     * @param callable|Schema $schema
     * @param bool            $ifNotExists
     * @param array           $options
     *
     * @return  $this
     */
    public function create($schema, $ifNotExists = true, $options = [])
    {
        $defaultOptions = [
            'auto_increment' => 1,
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ];

        $options = array_merge($defaultOptions, $options);
        $schema = $this->callSchema($schema);
        $columns = [];
        $primary = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn($column);

            $columns[$column->getName()] = MysqlGrammar::build(
                $column->getType() . $column->getLength(),
                $column->getSigned() ? '' : 'UNSIGNED',
                $column->getAllowNull() ? '' : 'NOT NULL',
                $column->getDefault() !== false ? 'DEFAULT ' . $this->db->getQuery(true)->validValue(
                        $column->getDefault()
                    ) : '',
                $column->getAutoIncrement() ? 'AUTO_INCREMENT' : '',
                $column->getComment() ? 'COMMENT ' . $this->db->quote($column->getComment()) : '',
                $column->getSuffix()
            );

            // Primary
            if ($column->isPrimary()) {
                $primary[] = $column->getName();
            }
        }

        $keys = [];

        foreach ($schema->getIndexes() as $index) {
            $name = $index->getName();
            $keys[$name] = [
                'type' => $index->getType(),
                'name' => $name,
                'columns' => $index->getColumns(),
                'comment' => $index->getComment() ? 'COMMENT ' . $this->db->quote($index->getComment()) : '',
            ];
        }

        $query = MysqlGrammar::createTable(
            $this->getName(),
            $columns,
            $primary,
            $keys,
            $options['auto_increment'],
            $ifNotExists,
            $options['engine'],
            $options['charset'],
            $options['collate']
        );

        $this->db->setQuery($query)->execute();

        return $this->reset();
    }

    /**
     * addColumn
     *
     * @param string $name
     * @param string $type
     * @param bool   $signed
     * @param bool   $allowNull
     * @param string $default
     * @param string $comment
     * @param array  $options
     *
     * @return  static
     */
    public function addColumn(
        $name,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = '',
        $comment = '',
        $options = []
    ) {
        $column = $name;

        if (!($column instanceof Column)) {
            $column = new Column($name, $type, $signed, $allowNull, $default, $comment, $options);
        }

        if ($this->hasColumn($column->getName())) {
            return $this;
        }

        $this->prepareColumn($column);

        $query = MysqlGrammar::addColumn(
            $this->getName(),
            $column->getName(),
            $column->getType() . $column->getLength(),
            $column->getSigned(),
            $column->getAllowNull(),
            $column->getDefault(),
            $column->getPosition(),
            $column->getComment()
        );

        // Add suffix
        $query = MysqlGrammar::build($query, $column->getSuffix());

        $this->db->setQuery($query)->execute();

        return $this->reset();
    }

    /**
     * modifyColumn
     *
     * @param string|Column $name
     * @param string        $type
     * @param bool          $signed
     * @param bool          $allowNull
     * @param string        $default
     * @param string        $comment
     * @param array         $options
     *
     * @return  static
     */
    public function modifyColumn(
        $name,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = '',
        $comment = '',
        $options = []
    ) {
        if ($name instanceof Column) {
            $column = $name;
            $length = $column->getLength();
            $name = $column->getName();
            $type = $column->getType();
            $signed = $column->getSigned();
            $allowNull = $column->getAllowNull();
            $default = $column->getDefault();
            $position = $column->getPosition();
            $comment = $column->getComment();
            $suffix = $column->getSuffix();
        } else {
            $position = isset($options['position']) ? $options['position'] : null;
            $suffix = isset($options['suffix']) ? $options['suffix'] : null;
        }

        if (!$this->hasColumn($name)) {
            return $this;
        }

        $type = MysqlType::getType($type);
        $length = isset($length) ? $length : MysqlType::getLength($type);
        $length = $length ? '(' . $length . ')' : null;

        $query = MysqlGrammar::modifyColumn(
            $this->getName(),
            $name,
            $type . $length,
            $signed,
            $allowNull,
            $default,
            $position,
            $comment
        );

        // Add suffix
        $query = MysqlGrammar::build($query, $suffix);

        $this->db->setQuery($query)->execute();

        return $this->reset();
    }

    /**
     * changeColumn
     *
     * @param string        $oldName
     * @param string|Column $newName
     * @param string        $type
     * @param bool          $signed
     * @param bool          $allowNull
     * @param string        $default
     * @param string        $comment
     * @param array         $options
     *
     * @return  static
     */
    public function changeColumn(
        $oldName,
        $newName,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = '',
        $comment = '',
        $options = []
    ) {
        if (!$this->hasColumn($oldName)) {
            return $this;
        }

        if ($newName instanceof Column) {
            $column = $newName;
            $length = $column->getLength();
            $newName = $column->getName();
            $type = $column->getType() . $length;
            $signed = $column->getSigned();
            $allowNull = $column->getAllowNull();
            $default = $column->getDefault();
            $position = $column->getPosition();
            $comment = $column->getComment();
            $suffix = $column->getSuffix();
        } else {
            $position = isset($options['position']) ? $options['position'] : null;
            $suffix = isset($options['suffix']) ? $options['suffix'] : null;
        }

        $type = MysqlType::getType($type);
        $length = isset($length) ? $length : MysqlType::getLength($type);
        $length = $length ? '(' . $length . ')' : null;

        $query = MysqlGrammar::changeColumn(
            $this->getName(),
            $oldName,
            $newName,
            $type . $length,
            $signed,
            $allowNull,
            $default,
            $position,
            $comment
        );

        // Add suffix
        $query = MysqlGrammar::build($query, $suffix);

        $this->db->setQuery($query)->execute();

        return $this->reset();
    }

    /**
     * addIndex
     *
     * @param string       $type
     * @param array|string $columns
     * @param string       $name
     * @param string       $comment
     * @param array        $options
     *
     * @return mixed
     */
    public function addIndex($type, $columns = [], $name = null, $comment = null, $options = [])
    {
        if ($this->hasIndex($name)) {
            $this->dropIndex($name);
        }

        if (!$type instanceof Key) {
            if (!$columns) {
                throw new \InvalidArgumentException('No columns given.');
            }

            $columns = (array) $columns;

            $index = new Key($type, $columns, $name, $comment);
        } else {
            $index = $type;
        }

        $query = MysqlGrammar::addIndex(
            $this->getName(),
            $index->getType(),
            $index->getColumns(),
            $index->getName(),
            $index->getComment()
        );

        $this->db->setQuery($query)->execute();

        return $this->reset();
    }

    /**
     * dropIndex
     *
     * @param string $name
     *
     * @return  static
     */
    public function dropIndex($name)
    {
        if (!$this->hasIndex($name)) {
            return $this;
        }

        $query = MysqlGrammar::dropIndex($this->getName(), $name);

        $this->db->setQuery($query)->execute();

        return $this->reset();
    }

    /**
     * rename
     *
     * @param string  $newName
     * @param boolean $returnNew
     *
     * @return  $this
     */
    public function rename($newName, $returnNew = true)
    {
        $this->db->setQuery(
            'RENAME TABLE ' . $this->db->quoteName($this->getName()) . ' TO ' . $this->db->quoteName($newName)
        );

        $this->db->execute();

        if ($returnNew) {
            return $this->db->getTable($newName);
        }

        return $this->reset();
    }

    /**
     * getColumnDetails
     *
     * @param bool $refresh
     *
     * @return mixed
     */
    public function getColumnDetails($refresh = false)
    {
        if (empty($this->columnCache) || $refresh) {
            $query = MysqlGrammar::showTableColumns($this->getName(), true);

            $this->columnCache = $this->db->setQuery($query)->loadAll('Field');
        }

        return $this->columnCache;
    }

    /**
     * getIndexes
     *
     * @return  array
     */
    public function getIndexes()
    {
        if (!$this->indexCache) {
            // Get the details columns information.
            $this->db->setQuery('SHOW KEYS FROM ' . $this->db->quoteName($this->getName()));

            $this->indexCache = $this->db->loadAll();
        }

        return $this->indexCache;
    }
}
