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
use Windwalker\Query\Mysql\MysqlQueryBuilder;

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
	public function create($schema, $ifNotExists = true, $options = array())
	{
		$defaultOptions = array(
			'auto_increment' => 1,
			'engine' => 'InnoDB',
			'charset' => 'utf8'
		);

		$options = array_merge($defaultOptions, $options);
		$schema  = $this->callSchema($schema);
		$columns = array();
		$primary = array();

		foreach ($schema->getColumns() as $column)
		{
			$column = $this->prepareColumn($column);

			$columns[$column->getName()] = MysqlQueryBuilder::build(
				$column->getType() . $column->getLength(),
				$column->getSigned() ? '' : 'UNSIGNED',
				$column->getAllowNull() ? '' : 'NOT NULL',
				$column->getDefault() !== false ? 'DEFAULT ' . $this->db->getQuery(true)->validValue($column->getDefault()) : '',
				$column->getAutoIncrement() ? 'AUTO_INCREMENT' : '',
				$column->getComment() ? 'COMMENT ' . $this->db->quote($column->getComment()) : ''
			);

			// Primary
			if ($column->isPrimary())
			{
				$primary[] = $column->getName();
			}
		}

		$keys = array();

		foreach ($schema->getIndexes() as $index)
		{
			$keys[$index->getName()] = array(
				'type' => $index->getType(),
				'name' => $index->getName(),
				'columns' => $index->getColumns(),
				'comment' => $index->getComment() ? 'COMMENT ' . $this->db->quote($index->getComment()) : ''
			);
		}

		$query = MysqlQueryBuilder::createTable($this->table, $columns, $primary, $keys, $options['auto_increment'], $ifNotExists, $options['engine'], $options['charset']);

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
	public function addColumn($name, $type = 'text', $signed = true, $allowNull = true, $default = '', $comment = '', $options = array())
	{
		$column = $name;

		if (!($column instanceof Column))
		{
			$column = new Column($name, $type, $signed, $allowNull, $default, $comment, $options);
		}

		$this->prepareColumn($column);

		$query = MysqlQueryBuilder::addColumn(
			$this->table,
			$column->getName(),
			$column->getType() . $column->getLength(),
			$column->getSigned(),
			$column->getAllowNull(),
			$column->getDefault(),
			$column->getPosition(),
			$column->getComment()
		);

		$this->db->setQuery($query)->execute();

		return $this->reset();
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
		if ($name instanceof Column)
		{
			$column    = $name;
			$length    = $column->getLength();
			$name      = $column->getName();
			$type      = $column->getType();
			$signed    = $column->getSigned();
			$allowNull = $column->getAllowNull();
			$default   = $column->getDefault();
			$position  = $column->getPosition();
			$comment   = $column->getComment();
		}
		else
		{
			$position = isset($options['position']) ? $options['position'] : null;
		}

		$type   = MysqlType::getType($type);
		$length = isset($length) ? $length : MysqlType::getLength($type);
		$length = $length ? '(' . $length . ')' : null;

		$query = MysqlQueryBuilder::modifyColumn(
			$this->table,
			$name,
			$type . $length,
			$signed,
			$allowNull,
			$default,
			$position,
			$comment
		);

		$this->db->setQuery($query)->execute();

		return $this->reset();
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
		if ($newName instanceof Column)
		{
			$column    = $newName;
			$length    = $column->getLength();
			$newName   = $column->getName();
			$type      = $column->getType() . $length;
			$signed    = $column->getSigned();
			$allowNull = $column->getAllowNull();
			$default   = $column->getDefault();
			$position  = $column->getPosition();
			$comment   = $column->getComment();
		}
		else
		{
			$position = isset($options['position']) ? $options['position'] : null;
		}

		$type   = MysqlType::getType($type);
		$length = isset($length) ? $length : MysqlType::getLength($type);
		$length = $length ? '(' . $length . ')' : null;

		$query = MysqlQueryBuilder::changeColumn(
			$this->table,
			$oldName,
			$newName,
			$type . $length,
			$signed,
			$allowNull,
			$default,
			$position,
			$comment
		);

		$this->db->setQuery($query)->execute();

		return $this->reset();
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
		if (!$type instanceof Key)
		{
			if (!$columns)
			{
				throw new \InvalidArgumentException('No columns given.');
			}

			$columns = (array) $columns;

			$name = $name ? : $columns[0];

			$index = new Key;

			$index->name($name)
				->type($type)
				->columns($columns)
				->comment($comment);
		}
		else
		{
			$index = $type;
		}

		$query = MysqlQueryBuilder::addIndex(
			$this->table,
			$index->getType(),
			$index->getName(),
			$index->getColumns(),
			$index->getComment()
		);

		$this->db->setQuery($query)->execute();

		return $this->reset();
	}

	/**
	 * dropIndex
	 *
	 * @param string  $name
	 *
	 * @return  static
	 */
	public function dropIndex($name)
	{
		if (!$this->hasIndex($name))
		{
			return $this;
		}
		
		$query = MysqlQueryBuilder::dropIndex($this->table, $name);

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
		$this->db->setQuery('RENAME TABLE ' . $this->db->quoteName($this->table) . ' TO ' . $this->db->quoteName($newName));

		$this->db->execute();

		if ($returnNew)
		{
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
		if (empty($this->columnCache) || $refresh)
		{
			$query = MysqlQueryBuilder::showTableColumns($this->table, true);

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
		if (!$this->indexCache)
		{
			// Get the details columns information.
			$this->db->setQuery('SHOW KEYS FROM ' . $this->db->quoteName($this->table));

			$this->indexCache = $this->db->loadAll();
		}

		return $this->indexCache;
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
		$typeMapper = $this->getTypeMapper();

		// Fix for Strict Mode
		if ($column->getType() == $typeMapper::DATETIME && $column->getDefault() === '')
		{
			$default = $this->db->getQuery(true)->getNullDate();

			$column->defaultValue($default);
		}

		$column = parent::prepareColumn($column);

		return $column;
	}
}
