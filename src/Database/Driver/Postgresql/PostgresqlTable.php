<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\DatabaseHelper;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Postgresql\PostgresqlGrammar;

/**
 * Class PostgresqlTable
 *
 * @since 2.0
 */
class PostgresqlTable extends AbstractTable
{
    /**
     * create
     *
     * @param callable|Schema $schema
     * @param bool            $ifNotExists
     * @param array           $options
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

            $columns[$column->getName()] = PostgresqlGrammar::build(
                $column->getType() . $column->getLength(),
                $column->getAllowNull() ? null : 'NOT NULL',
                $column->isPrimary()
                    ? null
                    : 'DEFAULT ' . $this->db->getQuery(true)->validValue($column->getDefault())
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

        $inherits = isset($options['inherits']) ? $options['inherits'] : null;
        $tablespace = isset($options['tablespace']) ? $options['tablespace'] : null;

        $query = PostgresqlGrammar::createTable(
            $this->getName(),
            $columns,
            $primary,
            $keys,
            $inherits,
            $ifNotExists,
            $tablespace
        );

        $comments = isset($options['comments']) ? $options['comments'] : [];
        $keyComments = isset($options['key_comments']) ? $options['key_comments'] : [];

        // Comments
        foreach ($comments as $name => $comment) {
            $query .= ";\n" . PostgresqlGrammar::comment('COLUMN', $this->getName(), $name, $comment);
        }

        foreach ($keyComments as $name => $comment) {
            $query .= ";\n" . PostgresqlGrammar::comment('INDEX', 'public', $name, $comment);
        }

        DatabaseHelper::batchQuery($this->db, $query);

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

        $query = PostgresqlGrammar::addColumn(
            $this->getName(),
            $column->getName(),
            $column->getType() . $column->getLength(),
            $column->getAllowNull(),
            $column->getDefault()
        );

        $this->db->setQuery($query)->execute();

        if ($column->getComment()) {
            $query = PostgresqlGrammar::comment('COLUMN', $this->getName(), $column->getName(), $column->getComment());
            $this->db->setQuery($query)->execute();
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
        /** @var PostgresqlType $typeMapper */
        $typeMapper = $this->getDataType();

        $type = $typeMapper::getType($column->getType());

        $length = $typeMapper::noLength($type) ? null : $column->getLength();

        $column->length($length);

        if ($column->getAutoIncrement()) {
            $column->type(PostgresqlType::SERIAL);
            $options['sequences'][$column->getName()] = $this->getName() . '_' . $column->getName() . '_seq';
        }

        return parent::prepareColumn($column);
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

        $type = PostgresqlType::getType($type);
        $length = isset($length) ? $length : PostgresqlType::getLength($type);
        $length = PostgresqlType::noLength($type) ? null : $length;
        $length = $length ? '(' . $length . ')' : null;

        $query = $this->db->getQuery(true);

        // Type
        $sql = PostgresqlGrammar::build(
            'ALTER TABLE ' . $query->quoteName($this->getName()),
            'ALTER COLUMN',
            $query->quoteName($name),
            'TYPE',
            $type . $length,
            $this->usingTextToNumeric($name, $type)
        );

        $sql .= ";\n" . PostgresqlGrammar::build(
            'ALTER TABLE ' . $query->quoteName($this->getName()),
            'ALTER COLUMN',
            $query->quoteName($name),
            $allowNull ? 'DROP' : 'SET',
            'NOT NULL'
        );

        if ($default !== false) {
            $sql .= ";\n" . PostgresqlGrammar::build(
                'ALTER TABLE ' . $query->quoteName($this->getName()),
                'ALTER COLUMN',
                $query->quoteName($name),
                'SET DEFAULT ' . $query->validValue($default)
            );
        }

        $sql .= ";\n" . PostgresqlGrammar::comment(
            'COLUMN',
            $this->getName(),
            $name,
            $comment
        );

        DatabaseHelper::batchQuery($this->db, $sql);

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

        $column = $name = $newName;

        if ($column instanceof Column) {
            $name = $column->getName();
            $type = $column->getType();
            $length = $column->getLength();
            $allowNull = $column->getAllowNull();
            $default = $column->getDefault();
            $comment = $column->getComment();
        }

        $type = PostgresqlType::getType($type);
        $length = isset($length) ? $length : PostgresqlType::getLength($type);
        $length = PostgresqlType::noLength($type) ? null : $length;
        $length = $length ? '(' . $length . ')' : null;

        $query = $this->db->getQuery(true);

        // Type
        $sql = PostgresqlGrammar::build(
            'ALTER TABLE ' . $query->quoteName($this->getName()),
            'ALTER COLUMN',
            $query->quoteName($oldName),
            'TYPE',
            $type . $length,
            $this->usingTextToNumeric($oldName, $type)
        );

        // Not NULL
        $sql .= ";\n" . PostgresqlGrammar::build(
            'ALTER TABLE ' . $query->quoteName($this->getName()),
            'ALTER COLUMN',
            $query->quoteName($oldName),
            $allowNull ? 'DROP' : 'SET',
            'NOT NULL'
        );

        // Default
        if ($default !== null) {
            $sql .= ";\n" . PostgresqlGrammar::build(
                'ALTER TABLE ' . $query->quoteName($this->getName()),
                'ALTER COLUMN',
                $query->quoteName($oldName),
                'SET DEFAULT' . $query->quote($default)
            );
        }

        // Comment
        $sql .= ";\n" . PostgresqlGrammar::comment(
            'COLUMN',
            $this->getName(),
            $oldName,
            $comment
        );

        // Rename
        $sql .= ";\n" . PostgresqlGrammar::renameColumn(
            $this->getName(),
            $oldName,
            $name
        );

        DatabaseHelper::batchQuery($this->db, $sql);

        return $this->reset();
    }

    /**
     * usingTextToNumeric
     *
     * @param   string $column
     * @param   string $type
     *
     * @return  string
     */
    protected function usingTextToNumeric($column, $type)
    {
        $type = strtolower($type);

        $details = $this->getColumnDetail($column);

        list($originType) = explode('(', $details->Type);

        $textTypes = [
            PostgresqlType::TEXT,
            PostgresqlType::CHAR,
            PostgresqlType::CHARACTER,
            PostgresqlType::VARCHAR,
        ];

        $numericTypes = [
            PostgresqlType::INTEGER,
            PostgresqlType::SMALLINT,
            PostgresqlType::FLOAT,
            PostgresqlType::DOUBLE,
            PostgresqlType::DECIMAL,
        ];

        if (in_array($originType, $textTypes, true) && in_array($type, $numericTypes, true)) {
            return sprintf('USING trim(%s)::%s', $this->db->quoteName($column), $type);
        }

        return null;
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

        $query = PostgresqlGrammar::addIndex(
            $this->getName(),
            $index->getType(),
            $index->getColumns(),
            $index->getName()
        );

        $this->db->setQuery($query)->execute();

        if ($index->getComment()) {
            $query = PostgresqlGrammar::comment('INDEX', 'public', $index->getName(), $index->getComment());

            $this->db->setQuery($query)->execute();
        }

        return $this->reset();
    }

    /**
     * dropIndex
     *
     * @param string $name
     * @param bool   $constraint
     *
     * @return static
     */
    public function dropIndex($name, $constraint = false)
    {
        if (!$constraint && !$this->hasIndex($name)) {
            return $this;
        }

        if ($constraint) {
            $query = PostgresqlGrammar::dropConstraint($this->getName(), $name, true, 'RESTRICT');
        } else {
            $query = PostgresqlGrammar::dropIndex($name, true);
        }

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
            PostgresqlGrammar::build(
                'ALTER TABLE',
                $this->db->quoteName($this->getName()),
                'RENAME TO',
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
        if (empty($this->columnCache) || $refresh) {
            $query = PostgresqlGrammar::showTableColumns($this->db->replacePrefix($this->getName()));

            $fields = $this->db->setQuery($query)->loadAll();

            $result = [];

            foreach ($fields as $field) {
                // Do some dirty translation to MySQL output.
                $result[$field->column_name] = (object) [
                    'column_name' => $field->column_name,
                    'type' => $field->column_type,
                    'null' => $field->Null,
                    'Default' => $field->Default,
                    'Field' => $field->column_name,
                    'Type' => $field->column_type,
                    'Null' => $field->Null,
                    'Extra' => null,
                    'Privileges' => null,
                    'Comment' => $field->Comment,
                    'Key' => '',
                ];
            }

            $keys = $this->getIndexes();

            foreach ($result as $field) {
                if (strpos($field->Default, 'nextval') !== false) {
                    $field->Extra = 'auto_increment';
                    $field->Default = 0;
                }

                if (preg_match('/^NULL::*/', (string) $field->Default)) {
                    $field->Default = null;
                }

                if (preg_match("/'(.*)'::[\w\s]/", (string) $field->Default, $matches)) {
                    $field->Default = $matches[1] ?? '';
                }

                if (strpos($field->Type, 'character varying') !== false) {
                    $field->Type = str_replace('character varying', 'varchar', $field->Type);
                }

                // Find key
                $index = null;

                foreach ($keys as $key) {
                    if ($key->column_name === $field->column_name) {
                        $index = $key;
                        break;
                    }
                }

                if ($index) {
                    if ($index->is_primary) {
                        $field->Key = 'PRI';
                    } elseif ($index->is_unique) {
                        $field->Key = 'UNI';
                    } else {
                        $field->Key = 'MUL';
                    }
                }
            }

            $this->columnCache = $result;
        }

        return $this->columnCache;
    }

    /**
     * getIndexes
     *
     * @return  mixed
     */
    public function getIndexes()
    {
        $this->db->setQuery(
            '
SELECT
    t.relname AS table_name,
    i.relname AS index_name,
    a.attname AS column_name,
    ix.indisunique AS is_unique,
    ix.indisprimary AS is_primary
FROM pg_class AS t,
    pg_class AS i,
    pg_index AS ix,
    pg_attribute AS a
WHERE t.oid = ix.indrelid
    AND i.oid = ix.indexrelid
    AND a.attrelid = t.oid
    AND a.attnum = ANY(ix.indkey)
    AND t.relkind = \'r\'
    AND t.relname = ' . $this->db->quote($this->db->replacePrefix($this->getName())) . '
ORDER BY t.relname, i.relname;'
        );

        $keys = $this->db->loadAll();

        foreach ($keys as $key) {
            $key->Table = $this->getName();
            $key->Non_unique = !$key->is_unique;
            $key->Key_name = $key->index_name;
            $key->Column_name = $key->column_name;
            $key->Collation = 'A';
            $key->Cardinality = 0;
            $key->Sub_part = null;
            $key->Packed = null;
            $key->Null = null;
            $key->Index_type = 'BTREE';
            // TODO: Finish comments query
            $key->Comment = null;
            $key->Index_comment = null;
        }

        return $keys;
    }

    /**
     * Get the details list of sequences for a table.
     *
     * @param   string $table The name of the table.
     *
     * @return  array  An array of sequences specification for the table.
     *
     * @since   2.1
     * @throws  \RuntimeException
     */
    public function getTableSequences($table)
    {
        // To check if table exists and prevent SQL injection
        $tableList = $this->db->getDatabase()->getTables();

        if (in_array($table, $tableList)) {
            $name = [
                's.relname AS sequence',
                'n.nspname AS schema',
                't.relname AS table',
                'a.attname AS column',
                'info.data_type AS data_type',
                'info.minimum_value AS minimum_value',
                'info.maximum_value AS maximum_value',
                'info.increment AS increment',
                'info.cycle_option AS cycle_option',
            ];

            if (version_compare($this->db->getVersion(), '9.1.0') >= 0) {
                $name[] .= 'info.start_value AS start_value';
            }

            // Get the details columns information.
            $query = $this->db->getQuery(true);

            $query->select($this->db->quoteName($name))
                ->from('pg_class AS s')
                ->leftJoin(
                    "pg_depend d ON d.objid=s.oid AND d.classid='pg_class'::regclass "
                    . "AND d.refclassid='pg_class'::regclass"
                )
                ->leftJoin('pg_class t ON t.oid=d.refobjid')
                ->leftJoin('pg_namespace n ON n.oid=t.relnamespace')
                ->leftJoin('pg_attribute a ON a.attrelid=t.oid AND a.attnum=d.refobjsubid')
                ->leftJoin('information_schema.sequences AS info ON info.sequence_name=s.relname')
                ->where("s.relkind='S' AND d.deptype='a' AND t.relname=" . $this->db->quote($table));

            $this->db->setQuery($query);

            $seq = $this->db->loadAll();

            return $seq;
        }

        return false;
    }
}
