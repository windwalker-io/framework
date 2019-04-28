<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Sqlsrv\SqlsrvGrammar;

/**
 * The SqlsrvTable class.
 *
 * @since  3.5
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
        if ($ifNotExists && $this->db->getDatabase(null, true)->tableExists($this->getName())) {
            return $this;
        }

        $defaultOptions = [
            'auto_increment' => 1
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
                $column->getAutoIncrement() ? 'identity' : null,
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
            if ($index->getType() === Key::TYPE_PRIMARY) {
                $primary = array_merge($primary, $index->getColumns());
            } else {
                $keys[$index->getName()] = [
                    'type' => strtoupper($index->getType()),
                    'name' => $index->getName(),
                    'columns' => $index->getColumns(),
                ];

                if ($index->getComment()) {
                    $keyComments[$index->getName()] = $index->getComment();
                }
            }
        }

        $options['comments'] = $comments;
        $options['key_comments'] = $keyComments;

        $query = SqlsrvGrammar::createTable(
            $this->getName(),
            $columns,
            $primary,
            $keys,
            false
        );

        $this->db->execute($query);
        $query = '';

        $comments = $options['comments'] ?? [];

        // Comments
        foreach ($comments as $name => $comment) {
            $query .= ";\n" . SqlsrvGrammar::comment(
                'COLUMN',
                $this->db->replacePrefix($this->getName()),
                $name,
                $comment
            );
        }

        if ($query) {
            $this->db->execute($query);
        }

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

        if (in_array($column->getType(), [SqlsrvType::TEXT, SqlsrvType::LONGTEXT], true)) {
            $column->length('max');
        }

        $column = parent::prepareColumn($column);

        if (SqlsrvType::noLength($column->getType())) {
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
     * @return  static
     */
    public function rename($newName, $returnNew = true)
    {
        $this->db->setQuery(
            SqlsrvGrammar::build(
                'exec sp_rename',
                $this->db->quoteName($this->getName()),
                ',',
                $this->db->quoteName($newName)
            )
        );

        $this->db->execute();

        if ($returnNew) {
            return $this->db->getTable($newName);
        }

        return $this;
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
        if (!$this->columnCache || $refresh) {
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

            $fields = $this->db->prepare($query)->loadAll();

            $keys = $this->getIndexes();

            foreach ($fields as $field) {
                $field->Key = '';
                $field->Extra = '';

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

                    if ($index->is_identity) {
                        $field->Extra = 'auto_increment';
                    }
                }
            }

            $this->columnCache = $details;
        }

        return $this->columnCache;
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

        $query = SqlsrvGrammar::addColumn(
            $this->getName(),
            $column->getName(),
            $column->getType() . $column->getLength(),
            $column->getAllowNull(),
            $column->getDefault()
        );

        $this->db->execute($query);

        if ($column->getComment()) {
            $query = SqlsrvGrammar::comment(
                'COLUMN',
                $this->db->replacePrefix($this->getName()),
                $column->getName(),
                $column->getComment()
            );
            $this->db->execute($query);
        }

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
        $column = $name;

        if ($column instanceof Column) {
            $name = $column->getName();
            $type = $column->getType();
            $length = $column->getLength();
            $allowNull = $column->getAllowNull();
            $default = $column->getDefault();
            $comment = $column->getComment();
        }

        if (!$this->hasColumn($name)) {
            return $this;
        }

        $type = SqlsrvType::getType($type);
        $length = $length ?? SqlsrvType::getLength($type);
        $length = SqlsrvType::noLength($type) ? null : $length;
        $length = $length ? '(' . $length . ')' : null;

        $query = $this->db->getQuery(true);

        // Sqlsrv must drop default constraint first
        $q = <<<SQL
DECLARE @ConstraintName nvarchar(200)
    
SELECT @ConstraintName = Name FROM SYS.DEFAULT_CONSTRAINTS
WHERE PARENT_OBJECT_ID = OBJECT_ID(%q)
AND PARENT_COLUMN_ID = (SELECT column_id FROM sys.columns
                        WHERE NAME = N%q
                        AND object_id = OBJECT_ID(N%q))
    
IF @ConstraintName IS NOT NULL
EXEC('ALTER TABLE %n DROP CONSTRAINT [' + @ConstraintName + ']')
SQL;

        $table = $this->db->replacePrefix($this->getName());

        $this->db->execute($query->format($q, $table, $name, $table, $table));

        // Type
        $this->db->execute(SqlsrvGrammar::build(
            'ALTER TABLE',
            $query->quoteName($this->getName()),
            'ALTER COLUMN',
            $query->quoteName($name),
            $type . $length,
            $allowNull ? null : 'NOT NULL'
        ));

        if ($default !== false) {
            $this->db->execute(SqlsrvGrammar::build(
                'ALTER TABLE',
                $query->quoteName($this->getName()),
                'ADD DEFAULT',
                $query->validValue($default),
                'FOR',
                $query->quoteName($name)
            ));
        }

        if ($comment) {
            $this->db->execute(SqlsrvGrammar::comment(
                'COLUMN',
                $this->db->replacePrefix($this->getName()),
                $column->getName(),
                $comment
            ));
        }

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

        $column = $newName;

        if ($column instanceof Column) {
            $newName = $column->getName();
            $column->name($oldName);

            $this->modifyColumn($column);
        } else {
            $this->modifyColumn(
                $oldName,
                $type,
                $signed,
                $allowNull,
                $default,
                $comment,
                $options
            );
        }

        $this->db->execute(SqlsrvGrammar::renameColumn(
            $this->db->replacePrefix($this->getName()),
            $oldName,
            $newName
        ));

        return $this;
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
        if (!$type instanceof Key) {
            if (!$columns) {
                throw new \InvalidArgumentException('No columns given.');
            }

            $columns = (array) $columns;

            $index = new Key($type, $columns, $name, $comment);
        } else {
            $index = $type;
        }

        if ($this->hasIndex($index->getName())) {
            $this->dropIndex($index->getName());
        }

        $query = SqlsrvGrammar::addIndex(
            $this->db->replacePrefix($this->getName()),
            strtolower($index->getType()) === 'key' ? 'INDEX' : $index->getType(),
            $index->getColumns(),
            $index->getName()
        );

        $this->db->execute($query);

        // No index comment now.
//        if ($index->getComment()) {
//            $query = SqlsrvGrammar::comment('INDEX', 'public', $index->getName(), $index->getComment());
//
//            $this->db->setQuery($query)->execute();
//        }

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
        if (strtolower($name) === 'identity') {
            throw new \LogicException('Unable to remove identity.');
        }

        if (strtolower($name) === 'primary') {
            $sql = <<<SQL
DECLARE @table NVARCHAR(512), @sql NVARCHAR(MAX);

SELECT @table = N%q;

SELECT @sql = 'ALTER TABLE ' + @table 
    + ' DROP CONSTRAINT ' + name + ';'
    FROM sys.key_constraints
    WHERE [type] = 'PK'
    AND [parent_object_id] = OBJECT_ID(@table);

EXEC sp_executeSQL @sql;
SQL;

            $this->db->execute(
                $this->db->getQuery(true)->format($sql, $this->db->replacePrefix($this->getName()))
            );

            return $this->reset();
        }

        if (!$this->hasIndex($name)) {
            return $this;
        }

        $this->db->execute(SqlsrvGrammar::build(
            'DROP INDEX',
            $this->db->getQuery(true)
                ->quoteName($this->getName() . '.' . $name)
        ));

        return $this->reset();
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
            'col.*',
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
            ->where('tbl.name = %q', $table)

            ->where('(idx.name IS NOT NULL OR col.is_identity = 1 OR idx.is_primary_key = 1)');

        $indexes = $this->db->prepare($query)->loadAll();

        $keys = [];

        foreach ($indexes as $index) {
            $key = new \stdClass();

            $key->Table = $table;
            $key->Is_primary = $index->is_primary_key ?: $index->is_identity;
            $key->Non_unique = !$index->is_unique;
            $key->Key_name = $key->Is_primary ? 'PRIMARY' : $index->index_name;
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
            $key->is_identity = $index->is_identity ? 'auto_increment' : '';

            $keys[] = $key;
        }

        return $keys;
    }
}
