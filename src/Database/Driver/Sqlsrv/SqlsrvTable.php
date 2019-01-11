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

        $column = parent::prepareColumn($column);

        if ($column->getType() === SqlsrvType::INTEGER) {
            $column->length(null);
        }

        if ($column->getType() === SqlsrvType::SMALLINT) {
            $column->length(null);
        }

        return $column;
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
        $details = [];
        $table = $this->db->replacePrefix((string) $this->name);

        $query = $this->db->getQuery(true);
        $query->select([
            'column_name AS Field',
            'data_type AS Type',
            'is_nullable AS ' . $query->quote('Null'),
            'column_default AS ' . $query->quote('Default'),
            'character_maximum_length',
            'numeric_precision',
            'numeric_scale',
        ])->from('information_schema.columns')
            ->where('table_name = %q', $table);

        $fields = $this->db->setQuery($query)->loadAll();

        $keys = $this->getIndexes();

        foreach ($fields as $field) {
            // Type & Length
            $length = ((int) $field->character_maximum_length) > 0
                ? $field->character_maximum_length
                : null;

            if ($field->numeric_precision) {
                $length = $field->numeric_precision;

                if ($field->numeric_scale) {
                    $length .= ',' . $field->numeric_scale;
                }
            }

            if ($length) {
                $field->Type .= '(' . $length . ')';
            }

            // Default
            // Parse ('foo') as foo
            $field->Default = preg_replace(
                "/(^(\(\(|\('|\(N'|\()|(('\)|(?<!\()\)\)|\))$))/i",
                '',
                $field->Default
            );

            $details[$field->Field] = $field;

            // TODO: Add AI info
            if (strpos($field->Default, 'nextval') !== false) {
//                $field->Extra = 'auto_increment';
            }

            // Find key
            $index = null;

            foreach ($keys as $key) {
                if ($key->Column_name == $field->Field) {
                    $index = $key;
                    break;
                }
            }

            if ($index) {
                if ($index->Is_primary) {
                    $field->Key = 'PRI';
                } elseif (!$index->Non_unique) {
                    $field->Key = 'UNI';
                } else {
                    $field->Key = 'MUL';
                }
            }
        }

        return $details;
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
        $query = $this->db->getQuery(true);
        $table = $this->db->replacePrefix($this->name);

        $query->select([
            'tbl.name AS table_name',
            'col.name AS column_name',
            'idx.name AS index_name',
            'idx.*'
        ])
            ->from('sys.columns AS col')
            ->leftJoin(
                'sys.tables AS tbl',
                'col.object_id = tbl.object_id'
            )
            ->leftJoin(
                'sys.index_columns AS ic',
                'col.column_id = ic.column_id AND ic.object_id = tbl.object_id'
            )
            ->leftJoin(
                'sys.indexes AS idx',
                'idx.object_id = tbl.object_id AND idx.index_id = ic.index_id'
            )
            ->where('tbl.name = %q', $table);

        $indexes = $this->db->setQuery($query)->loadAll();

        $keys = [];

        foreach ($indexes as $index) {
            $key = new \stdClass();

            $key->Table = $table;
            $key->Is_primary = $index->is_primary_key;
            $key->Non_unique = !$index->is_unique;
            $key->Key_name = $index->index_name;
            $key->Column_name = $index->column_name;
            $key->Collation = 'A';
            $key->Cardinality = 0;
            $key->Sub_part = null;
            $key->Packed = null;
            $key->Null = null;
            $key->Index_type = 'BTREE';
            // TODO: Finish comments query
            $key->Comment = null;
            $key->Index_comment = null;

            $keys[] = $key;
        }

        return $keys;
    }
}
