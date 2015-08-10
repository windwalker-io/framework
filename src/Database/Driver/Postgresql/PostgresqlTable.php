<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\DatabaseHelper;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;
use Windwalker\Query\Mysql\MysqlQueryBuilder;
use Windwalker\Query\Postgresql\PostgresqlQueryBuilder;

/**
 * Class PostgresqlTable
 *
 * @since 2.0
 */
class PostgresqlTable extends AbstractTable
{
	/**
	 * A cache to store Table columns.
	 *
	 * @var array
	 */
	protected $columnCache = array();

	/**
	 * Property columns.
	 *
	 * @var  Column[]
	 */
	protected $columns = array();

	/**
	 * Property indexes.
	 *
	 * @var  Key[]
	 */
	protected $indexes = array();

	/**
	 * Property primary.
	 *
	 * @var  array
	 */
	protected $primary = array();

	/**
	 * create
	 *
	 * @param bool  $ifNotExists
	 * @param array $options
	 *
	 * @return  static
	 */
	public function create($ifNotExists = true, $options = array())
	{
		$defaultOptions = array(
			'auto_increment' => 1,
			'sequences' => array()
		);

		$options = array_merge($defaultOptions, $options);

		$columns = array();
		$comments = array();

		foreach ($this->columns as $column)
		{
			$length = $column->getLength();

			$length = $length ? '(' . $length . ')' : null;

			if ($column->getAutoIncrement())
			{
				$column->type(PostgresqlType::SERIAL);
				$options['sequences'][$column->getName()] = $this->table . '_' . $column->getName() . '_seq';
			}

			$columns[$column->getName()] = MysqlQueryBuilder::build(
				$column->getType() . $length,
				$column->getAllowNull() ? null : 'NOT NULL',
				$column->getDefault() ? 'DEFAULT ' . $this->db->quote($column->getDefault()) : null
			);

			if ($column->getComment())
			{
				$comments[$column->getName()] = $column->getComment();
			}
		}

		$keys = array();
		$keyComments = array();

		foreach ($this->indexes as $index)
		{
			$keys[$index->getName()] = array(
				'type' => strtoupper($index->getType()),
				'name' => $index->getName(),
				'columns' => $index->getColumns()
			);

			if ($index->getComment())
			{
				$keyComments[$index->getName()] = $index->getComment();
			}
		}

		$options['comments'] = $comments;
		$options['key_comments'] = $keyComments;

		$this->doCreate($columns, $this->primary, $keys, $options['auto_increment'], $ifNotExists, $options);

		return $this;
	}

	/**
	 * update
	 *
	 * @return  static
	 */
	public function update()
	{
		foreach ($this->columns as $column)
		{
			$length = $column->getLength();

			$length = $length ? '(' . $length . ')' : null;

			$sequence = null;

			if ($column->getAutoIncrement())
			{
				$column->type(PostgresqlType::SERIAL);
			}

			$query = PostgresqlQueryBuilder::addColumn(
				$this->table,
				$column->getName(),
				$column->getType() . $length,
				$column->getAllowNull(),
				$column->getDefault()
			);

			$this->db->setQuery($query)->execute();

			if ($column->getComment())
			{
				$query = PostgresqlQueryBuilder::comment('COLUMN', $this->table, $column->getName(), $column->getComment());

				$this->db->setQuery($query)->execute();
			}
		}

		foreach ($this->indexes as $index)
		{
			$query = PostgresqlQueryBuilder::addIndex(
				$this->table,
				$index->getType(),
				$index->getName(),
				$index->getColumns()
			);

			$this->db->setQuery($query)->execute();

			if ($index->getComment())
			{
				$query = PostgresqlQueryBuilder::comment('INDEX', 'public', $index->getName(), $index->getComment());

				$this->db->setQuery($query)->execute();
			}
		}

		return $this;
	}

	/**
	 * save
	 *
	 * @param bool  $ifNotExists
	 * @param array $options
	 *
	 * @return  $this
	 */
	public function save($ifNotExists = true, $options = array())
	{
		if ($this->exists())
		{
			$this->update();
		}
		else
		{
			$this->create($ifNotExists, $options);
		}

		return $this;
	}

	/**
	 * drop
	 *
	 * @param bool   $ifNotExists
	 * @param string $option
	 *
	 * @return  static
	 */
	public function drop($ifNotExists = true, $option = '')
	{
		$query = MysqlQueryBuilder::dropTable($this->table, $ifNotExists, $option);

		$this->db->setQuery($query)->execute();

		return $this;
	}

	/**
	 * reset
	 *
	 * @return  static
	 */
	public function reset()
	{
		$this->columns = array();
		$this->primary = array();
		$this->indexes = array();

		return $this;
	}

	/**
	 * exists
	 *
	 * @return  boolean
	 */
	public function exists()
	{
		$database = $this->db->getDatabase();

		return $database->tableExists($this->table);
	}

	/**
	 * getDetail
	 *
	 * @return  array|boolean
	 */
	public function getDetail()
	{
		return $this->db->getDatabase()->getTableDetail($this->table);
	}

	/**
	 * create
	 *
	 * @param string $columns
	 * @param array  $pks
	 * @param array  $keys
	 * @param int    $autoIncrement
	 * @param bool   $ifNotExists
	 * @param array  $options
	 *
	 * @return $this
	 */
	public function doCreate($columns, $pks = array(), $keys = array(), $autoIncrement = null, $ifNotExists = true,
		$options = array())
	{
		$inherits = isset($options['inherits']) ? $options['inherits'] : null;
		$tablespace = isset($options['tablespace']) ? $options['tablespace'] : null;

		$query = PostgresqlQueryBuilder::createTable($this->table, $columns, $pks, $keys, $inherits, $ifNotExists, $tablespace);

		$comments = isset($options['comments']) ? $options['comments'] : array();
		$keyComments = isset($options['key_comments']) ? $options['key_comments'] : array();

		// Comments
		foreach ($comments as $name => $comment)
		{
			$query .= ";\n" . PostgresqlQueryBuilder::comment('COLUMN', $this->table, $name, $comment);
		}

		foreach ($keyComments as $name => $comment)
		{
			$query .= ";\n" . PostgresqlQueryBuilder::comment('INDEX', 'public', $name, $comment);
		}

		DatabaseHelper::batchQuery($this->db, $query);

		return $this;
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
	public function addColumn($name, $type = 'text', $signed = true, $allowNull = true, $default = '', $comment = '', $options = array())
	{
		$column = $name;

		if (!($column instanceof Column))
		{
			$column = new Column($name, $type, $signed, $allowNull, $default, $comment, $options);
		}

		$type   = PostgresqlType::getType($column->getType());
		$length = $column->getLength() ? : PostgresqlType::getLength($type);

		$length = PostgresqlType::noLength($type) ? null : $length;

		$column->type($type)
			->length($length);

		if ($column->isPrimary())
		{
			$this->primary[] = $column->getName();
		}

		$this->columns[] = $column;

		return $this;
	}

	/**
	 * dropColumn
	 *
	 * @param string $name
	 *
	 * @return  mixed
	 */
	public function dropColumn($name)
	{
		$query = PostgresqlQueryBuilder ::dropColumn($this->table, $name);

		$this->db->setQuery($query)->execute();

		return $this;
	}

	/**
	 * modifyColumn
	 *
	 * @param string|Column $name
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 *
	 * @return  static
	 */
	public function modifyColumn($name, $type = 'text', $signed = true, $allowNull = true, $default = '', $comment = '', $options = array())
	{
		$column = $name;

		if ($column instanceof Column)
		{
			$name      = $column->getName();
			$type      = $column->getType();
			$length    = $column->getLength();
			$allowNull = $column->getAllowNull();
			$default   = $column->getDefault();
			$comment   = $column->getComment();
		}

		$type   = PostgresqlType::getType($type);
		$length = isset($length) ? $length : PostgresqlType::getLength($type);
		$length = PostgresqlType::noLength($type) ? null : $length;
		$length = $length ? '(' . $length . ')' : null;

		$query = $this->db->getQuery(true);

		// Type
		$sql = PostgresqlQueryBuilder::build(
			'ALTER TABLE ' . $query->quoteName($this->table),
			'ALTER COLUMN',
			$query->quoteName($name),
			'TYPE',
			$type . $length,
			$this->usingTextToNumeric($name, $type)
		);

		$sql .= ";\n" . PostgresqlQueryBuilder::build(
			'ALTER TABLE ' . $query->quoteName($this->table),
			'ALTER COLUMN',
			$query->quoteName($name),
			$allowNull ? 'DROP' : 'SET',
			'NOT NULL'
		);

		if (!is_null($default))
		{
			$sql .= ";\n" . PostgresqlQueryBuilder::build(
				'ALTER TABLE ' . $query->quoteName($this->table),
				'ALTER COLUMN',
				$query->quoteName($name),
				'SET DEFAULT' . $query->quote($default)
			);
		}

		$sql .= ";\n" . PostgresqlQueryBuilder::comment(
			'COLUMN',
			$this->table,
			$name,
			$comment
		);

		DatabaseHelper::batchQuery($this->db, $sql);

		return $this;
	}

	/**
	 * changeColumn
	 *
	 * @param string $oldName
	 * @param string|Column  $newName
	 * @param string $type
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 *
	 * @return  static
	 */
	public function changeColumn($oldName, $newName, $type = 'text', $signed = true, $allowNull = true, $default = '', $comment = '', $options = array())
	{
		$column = $name = $newName;

		if ($column instanceof Column)
		{
			$name      = $column->getName();
			$type      = $column->getType();
			$length    = $column->getLength();
			$allowNull = $column->getAllowNull();
			$default   = $column->getDefault();
			$comment   = $column->getComment();
		}

		$type   = PostgresqlType::getType($type);
		$length = isset($length) ? $length : PostgresqlType::getLength($type);
		$length = PostgresqlType::noLength($type) ? null : $length;
		$length = $length ? '(' . $length . ')' : null;

		$query = $this->db->getQuery(true);

		// Type
		$sql = PostgresqlQueryBuilder::build(
			'ALTER TABLE ' . $query->quoteName($this->table),
			'ALTER COLUMN',
			$query->quoteName($oldName),
			'TYPE',
			$type . $length,
			$this->usingTextToNumeric($oldName, $type)
		);

		// Not NULL
		$sql .= ";\n" . PostgresqlQueryBuilder::build(
			'ALTER TABLE ' . $query->quoteName($this->table),
			'ALTER COLUMN',
			$query->quoteName($oldName),
			$allowNull ? 'DROP' : 'SET',
			'NOT NULL'
		);

		// Default
		if (!is_null($default))
		{
			$sql .= ";\n" . PostgresqlQueryBuilder::build(
				'ALTER TABLE ' . $query->quoteName($this->table),
				'ALTER COLUMN',
				$query->quoteName($oldName),
				'SET DEFAULT' . $query->quote($default)
			);
		}

		// Comment
		$sql .= ";\n" . PostgresqlQueryBuilder::comment(
			'COLUMN',
			$this->table,
			$oldName,
			$comment
		);

		// Rename
		$sql .= ";\n" . PostgresqlQueryBuilder::renameColumn(
			$this->table,
			$oldName,
			$name
		);

		DatabaseHelper::batchQuery($this->db, $sql);

		return $this;
	}

	/**
	 * usingTextToNumeric
	 *
	 * @param   string  $column
	 * @param   string  $type
	 *
	 * @return  string
	 */
	protected function usingTextToNumeric($column, $type)
	{
		$type = strtolower($type);

		$details = $this->getColumnDetail($column);

		list($originType) = explode('(', $details->Type);

		$textTypes = array(
			PostgresqlType::TEXT,
			PostgresqlType::CHAR,
			PostgresqlType::CHARACTER,
			PostgresqlType::VARCHAR
		);

		$numericTypes = array(
			PostgresqlType::INTEGER,
			PostgresqlType::SMALLINT,
			PostgresqlType::FLOAT,
			PostgresqlType::DOUBLE,
			PostgresqlType::DECIMAL
		);

		if (in_array($originType, $textTypes) && in_array($type, $numericTypes))
		{
			return sprintf('USING trim(%s)::%s', $this->db->quoteName($column), $type);
		}

		return null;
	}

	/**
	 * addIndex
	 *
	 * @param string       $type
	 * @param string       $name
	 * @param array|string $columns
	 * @param string       $comment
	 * @param array        $options
	 *
	 * @throws  \InvalidArgumentException
	 * @return  mixed
	 */
	public function addIndex($type, $name = null, $columns = array(), $comment = null, $options = array())
	{
		if (!$columns)
		{
			throw new \InvalidArgumentException('No columns given.');
		}

		$columns = (array) $columns;

		$name = $name ? : $columns[0];

		$index = new Key;

		$index->setName($name)
			->setType($type)
			->setColumns($columns)
			->setComment($comment);

		$this->indexes[] = $index;

		return $this;
	}

	/**
	 * dropIndex
	 *
	 * @param string $name
	 * @param bool   $constraint
	 *
	 * @return mixed
	 */
	public function dropIndex($name, $constraint = false)
	{
		if ($constraint)
		{
			$query = PostgresqlQueryBuilder::dropConstraint($this->table, $name, true, 'RESTRICT');
		}
		else
		{
			$query = PostgresqlQueryBuilder::dropIndex($name, true);
		}

		$this->db->setQuery($query)->execute();

		return $this;
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
		$this->db->setQuery(PostgresqlQueryBuilder::build(
			'ALTER TABLE',
			$this->db->quoteName($this->table),
			'RENAME TO',
			$this->db->quoteName($newName)
		));

		$this->db->execute();

		if ($returnNew)
		{
			return $this->db->getTable($newName);
		}

		return $this;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @return  static  Returns this object to support chaining.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function lock()
	{
		$this->db->setQuery('LOCK TABLES ' . $this->db->quoteName($this->table) . ' WRITE');

		return $this;
	}

	/**
	 * unlock
	 *
	 * @return  static  Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function unlock()
	{
		$this->db->setQuery('UNLOCK TABLES')->execute();

		return $this;
	}

	/**
	 * Method to truncate a table.
	 *
	 * @return  static
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function truncate()
	{
		$this->db->setQuery('TRUNCATE TABLE ' . $this->db->quoteName($this->table))->execute();

		return $this;
	}

	/**
	 * Get table columns.
	 *
	 * @param bool $refresh
	 *
	 * @return  array Table columns with type.
	 */
	public function getColumns($refresh = false)
	{
		if (empty($this->columnCache) || $refresh)
		{
			$this->columnCache = array_keys($this->getColumnDetails());
		}

		return $this->columnCache;
	}

	/**
	 * getColumnDetails
	 *
	 * @param bool $full
	 *
	 * @return  mixed
	 */
	public function getColumnDetails($full = true)
	{
		$query = PostgresqlQueryBuilder::showTableColumns($this->db->replacePrefix($this->table), $full);

		$fields = $this->db->setQuery($query)->loadAll();

		$result = array();

		foreach ($fields as $field)
		{
			// Do some dirty translation to MySQL output.
			$result[$field->column_name] = (object) array(
				'column_name' => $field->column_name,
				'type'        => $field->column_type,
				'null'        => $field->Null,
				'Default'     => $field->Default,
				'Field'       => $field->column_name,
				'Type'        => $field->column_type,
				'Null'        => $field->Null,
				'Extra'       => null,
				'Privileges'  => null,
				'Comment'     => $field->Comment
			);
		}

		$keys = $this->getIndexes();

		foreach ($result as $field)
		{
			if (preg_match("/^NULL::*/", $field->Default))
			{
				$field->Default = null;
			}

			if (strpos($field->Type, 'character varying') !== false)
			{
				$field->Type = str_replace('character varying', 'varchar', $field->Type);
			}

			if (strpos($field->Default, 'nextval') !== false)
			{
				$field->Extra = 'auto_increment';
			}

			// Find key
			$index = null;

			foreach ($keys as $key)
			{
				if ($key->column_name == $field->column_name)
				{
					$index = $key;
					break;
				}
			}

			if ($index)
			{
				if ($index->is_primary)
				{
					$field->Key = 'PRI';
				}
				elseif ($index->is_unique)
				{
					$field->Key = 'UNI';
				}
				else
				{
					$field->Key = 'MUL';
				}
			}
		}

		return $result;
	}

	/**
	 * getColumnDetail
	 *
	 * @param string $column
	 * @param bool   $full
	 *
	 * @return  mixed
	 */
	public function getColumnDetail($column, $full = true)
	{
		$columns = $this->getColumnDetails($full);

		return isset($columns[$column]) ? $columns[$column] : null;
	}

	/**
	 * getIndexes
	 *
	 * @return  mixed
	 */
	public function getIndexes()
	{
		$this->db->setQuery('
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
	AND t.relname = ' . $this->db->quote($this->db->replacePrefix($this->table)) . '
ORDER BY t.relname, i.relname;');

		$keys = $this->db->loadAll();

		foreach ($keys as $key)
		{
			$key->Table = $this->table;
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
	 * Method to get property Primary
	 *
	 * @return  array
	 */
	public function getPrimary()
	{
		return $this->primary;
	}

	/**
	 * Method to set property primary
	 *
	 * @param   array $primary
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPrimary($primary)
	{
		$this->primary = (array) $primary;

		return $this;
	}

	/**
	 * Get the details list of sequences for a table.
	 *
	 * @param   string  $table  The name of the table.
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

		if ( in_array($table, $tableList) )
		{
			$name = array('s.relname', 'n.nspname', 't.relname', 'a.attname', 'info.data_type',
				'info.minimum_value', 'info.maximum_value', 'info.increment', 'info.cycle_option');

			$as = array('sequence', 'schema', 'table', 'column', 'data_type',
				'minimum_value', 'maximum_value', 'increment', 'cycle_option');

			if (version_compare($this->db->getVersion(), '9.1.0') >= 0)
			{
				$name[] .= 'info.start_value';
				$as[] .= 'start_value';
			}

			// Get the details columns information.
			$query = $this->db->getQuery(true);

			$query->select($this->db->quoteName($name, $as))
				->from('pg_class AS s')
				->leftJoin("pg_depend d ON d.objid=s.oid AND d.classid='pg_class'::regclass AND d.refclassid='pg_class'::regclass")
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
