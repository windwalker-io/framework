<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Command\Table\Column;
use Windwalker\Database\Command\Table\Key;
use Windwalker\Query\Mysql\MysqlQueryBuilder;

/**
 * Class MysqlTable
 *
 * @since {DEPLOY_VERSION}
 */
class MysqlTable extends AbstractTable
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
			'engine' => 'InnoDB',
			'default_charset' => 'utf8'
		);

		$options = array_merge($defaultOptions, $options);

		$columns = array();

		foreach ($this->columns as $column)
		{
			$length = $column->getLength();

			$length = $length ? '(' . $length . ')' : null;

			$columns[$column->getName()] = MysqlQueryBuilder::build(
				$column->getType() . $length,
				$column->getSigned() ? '' : 'UNSIGNED',
				$column->getAllowNull() ? '' : 'NOT NULL',
				$column->getDefault() ? 'DEFAULT ' . $this->db->quote($column->getDefault()) : '',
				$column->getAutoIncrement() ? 'AUTO_INCREMENT' : '',
				$column->getComment() ? 'COMMENT ' . $this->db->quote($column->getComment()) : ''
			);
		}

		$keys = array();

		foreach ($this->indexes as $index)
		{
			$keys[$index->getName()] = array(
				'type' => $index->getType(),
				'name' => $index->getName(),
				'columns' => $index->getColumns(),
				'comment' => $index->getComment() ? 'COMMENT ' . $this->db->quote($index->getComment()) : ''
			);
		}

		$this->doCreate($columns, $this->primary, $keys, $options['auto_increment'], $ifNotExists, $options['engine'], $options['default_charset']);

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
			$query = MysqlQueryBuilder::addColumn(
				$this->table,
				$column->getName(),
				$column->getType(),
				!$column->getSigned(),
				!$column->getAllowNull(),
				$column->getDefault(),
				$column->getPosition(),
				$column->getComment()
			);

			$this->db->setQuery($query)->execute();
		}

		foreach ($this->indexes as $index)
		{
			$query = MysqlQueryBuilder::addIndex(
				$this->table,
				$index->getType(),
				$index->getName(),
				$index->getColumns(),
				$index->getComment()
			);

			$this->db->setQuery($query)->execute();
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
	 * @param string $engine
	 * @param string $defaultCharset
	 *
	 * @return  $this
	 */
	public function doCreate($columns, $pks = array(), $keys = array(), $autoIncrement = null, $ifNotExists = true,
		$engine = 'InnoDB', $defaultCharset = 'utf8')
	{
		$query = MysqlQueryBuilder::createTable($this->table, $columns, $pks, $keys, $autoIncrement, $ifNotExists, $engine, $defaultCharset);

		$this->db->setQuery($query)->execute();

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
		$defaultOptions = array(
			'primary' => false,
			'auto_increment' => false,
			'position' => null,
			'length' => null
		);

		$options = array_merge($defaultOptions, $options);

		if ($options['primary'])
		{
			$options['auto_increment'] = true;

			$signed = false;
			$allowNull = false;

			$this->primary[] = $name;
		}

		$type = MysqlType::getType($type);

		$length = $options['length'] ? : MysqlType::getLength($type);

		$column = new Column;

		$column->setName($name)
			->setType($type)
			->setLength($length)
			->setSigned($signed)
			->setAllowNull($allowNull)
			->setDefault($default)
			->setComment($comment)
			->setAutoIncrement($options['auto_increment'])
			->setPosition($options['position']);

		// $query = MysqlQueryBuilder::addColumn($this->table, $name, $type, $unsigned, $notNull, $default, $position, $comment);

		$this->columns[] = $column;

		// $this->db->setQuery($query)->execute();

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
		$query = MysqlQueryBuilder::dropColumn($this->table, $name);

		$this->db->setQuery($query)->execute();

		return $this;
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
	 * @param string  $type
	 * @param string  $name
	 *
	 * @return  mixed
	 */
	public function dropIndex($type, $name)
	{
		$query = MysqlQueryBuilder::dropIndex($this->table, $type, $name);

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
		$this->db->setQuery('RENAME TABLE ' . $this->db->quoteName($this->table) . ' TO ' . $this->db->quoteName($newName));

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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
		$query = MysqlQueryBuilder::showTableColumns($this->table, $full);

		return $this->db->setQuery($query)->loadAll('Field');
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
		$query = MysqlQueryBuilder::showTableColumns($this->table, $full, 'Field = ' . $this->db->quote($column));

		return $this->db->setQuery($query)->loadOne();
	}

	/**
	 * getIndexes
	 *
	 * @return  mixed
	 */
	public function getIndexes()
	{
		// Get the details columns information.
		$this->db->setQuery('SHOW KEYS FROM ' . $this->db->quoteName($this->table));

		return $this->db->loadAll();
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
}

