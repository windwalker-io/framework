<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Sqlsrv\SqlsrvGrammar;

/**
 * The SqlsrvTable class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SqlsrvTable extends AbstractTable
{
    /**
     * create
     *
     * @param   callable|Schema $schema
     * @param   bool            $ifNotExists
     * @param   array           $options
     *
     * @return  static
     */
    public function create($schema, $ifNotExists = true, $options = [])
    {
        $defaultOptions = [
            'auto_increment' => 1,
            'sequences' => [],
        ];

        $options = array_merge($defaultOptions, $options);
        $schema = $this->callSchema($schema);
        $columns = [];
        $comments = [];
        $primary = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn($column);

            $columns[$column->getName()] = SqlsrvGrammar::build(
                $column->getType() . $column->getLength(),
                $column->getAllowNull() ? null : 'NOT NULL',
                $column->getDefault() ? 'DEFAULT ' . $this->db->quote($column->getDefault()) : null
            );

            // Comment
            if ($column->getComment()) {
                $comments[$column->getName()] = $column->getComment();
            }

            // Primary
            if ($column->isPrimary()) {
                $primary[] = $column->getName();
            }
        }

        $keys = [];
        $keyComments = [];

        foreach ($schema->getIndexes() as $index) {
            $keys[$index->getName()] = [
                'type' => strtoupper($index->getType()),
                'name' => $index->getName(),
                'columns' => $index->getColumns(),
            ];

            if ($index->getComment()) {
                $keyComments[$index->getName()] = $index->getComment();
            }
        }

        $options['comments'] = $comments;
        $options['key_comments'] = $keyComments;

//        $inherits = isset($options['inherits']) ? $options['inherits'] : null;
//        $tablespace = isset($options['tablespace']) ? $options['tablespace'] : null;

        $query = SqlsrvGrammar::createTable(
            $this->getName(),
            $columns,
            $primary,
            $keys,
//            $inherits,
            $ifNotExists
//            $tablespace
        );

//        $comments = isset($options['comments']) ? $options['comments'] : [];
//        $keyComments = isset($options['key_comments']) ? $options['key_comments'] : [];
//
//        // Comments
//        foreach ($comments as $name => $comment) {
//            $query .= ";\n" . SqlsrvGrammar::comment('COLUMN', $this->getName(), $name, $comment);
//        }
//
//        foreach ($keyComments as $name => $comment) {
//            $query .= ";\n" . SqlsrvGrammar::comment('INDEX', 'public', $name, $comment);
//        }
show($query);
        $this->db->setQuery($query)->execute();

        return $this->reset();
    }

    /**
     * prepareColumn
     *
     * @param Column $column
     *
     * @return  Column
     */
    protected function prepareColumn(Column $column)
    {
        if ($column->getType() === SqlsrvType::FLOAT && strpos($column->getLength(), ',') !== false) {
            $column->length(24);
        }

        if ($column->getType() === SqlsrvType::DOUBLE && strpos($column->getLength(), ',') !== false) {
            $column->length(53);
        }

        return parent::prepareColumn($column);
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
    }

    /**
     * addIndex
     *
     * @param string $type
     * @param array  $columns
     * @param string $name
     * @param string $comment
     * @param array  $options
     *
     * @return static
     */
    public function addIndex($type, $columns = [], $name = null, $comment = null, $options = [])
    {
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
    }

    /**
     * getIndexes
     *
     * @return  array
     */
    public function getIndexes()
    {
    }
}
