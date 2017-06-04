<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Database\Query\QueryHelper;
use Windwalker\Query\Query;

/**
 * Class DatabaseWriter
 *
 * @since 2.0
 */
abstract class AbstractWriter
{
	/**
	 * Property driver.
	 *
	 * @var  \Windwalker\Database\Driver\AbstractDatabaseDriver
	 */
	protected $db;

	/**
	 * Property cursor.
	 *
	 * @var  resource
	 */
	protected $cursor;

	/**
	 * Constructor.
	 *
	 * @param AbstractDatabaseDriver $db
	 */
	public function __construct(AbstractDatabaseDriver $db)
	{
		$this->db = $db;
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string        $table  The name of the database table to insert into.
	 * @param   array|object  &$data  A reference to an object whose public properties match the table fields.
	 * @param   string        $key    The name of the primary key. If provided the object property is updated.
	 *
	 * @throws \InvalidArgumentException
	 * @return  static
	 *
	 * @since   2.0
	 */
	public function insertOne($table, &$data, $key = null)
	{
		$fields = [];
		$values = [];
		$item = [];

		if (!is_array($data) && !is_object($data))
		{
			throw new \InvalidArgumentException('Please give me array or object to insert.');
		}

		if ($data instanceof \Traversable)
		{
			$item = iterator_to_array($data);
		}
		elseif (is_object($data))
		{
			$item = get_object_vars($data);
		}
		else
		{
			$item = $data;
		}

		$query = $this->db->getQuery(true);

		// Iterate over the object variables to build the query fields and values.
		foreach ($item as $k => $v)
		{
			// Convert stringable object
			if (is_object($v) && is_callable([$v, '__toString']))
			{
				$v = (string) $v;
			}

			// Only process non-null scalars.
			if (is_array($v) || is_object($v))
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] === '_')
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $query->quoteName($k);
			$values[] = $v === null ? 'NULL' : $query->quote($v);
		}

		// Create the base insert statement.
		$query->insert($query->quoteName($table))
			->columns($fields)
			->values([$values]);

		// Set the query and execute the insert.
		$this->execute($query);

		// Update the primary key if it exists.
		$id = $this->insertId();

		if ($key && $id && is_string($key))
		{
			if (is_array($data))
			{
				$data[$key] = $id;
			}
			else
			{
				$data->$key = $id;
			}
		}

		return $data;
	}

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   string         $table       The name of the database table to update.
	 * @param   array|object   $data        A reference to an object whose public properties match the table fields.
	 * @param   array          $key         The name of the primary key.
	 * @param   boolean        $updateNulls True to update null fields or false to ignore them.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.0
	 */
	public function updateOne($table, $data, $key, $updateNulls = false)
	{
		if (!is_array($data) && !is_object($data))
		{
			throw new \InvalidArgumentException('Please give me array or object to update.');
		}

		if ($data instanceof \Traversable)
		{
			$item = iterator_to_array($data);
		}
		elseif (is_object($data))
		{
			$item = get_object_vars($data);
		}
		else
		{
			$item = $data;
		}

		$query = $this->db->getQuery(true);

		$key = (array) $key;

		// Create the base update statement.
		$query->update($query->quoteName($table));

		// Iterate over the object variables to build the query fields/value pairs.
		foreach ($item as $k => $v)
		{
			// Convert stringable object
			if (is_object($v) && is_callable([$v, '__toString']))
			{
				$v = (string) $v;
			}

			// Only process scalars that are not internal fields.
			if (is_array($v) || is_object($v) || $k[0] == '_')
			{
				continue;
			}

			// Set the primary key to the WHERE clause instead of a field to update.
			if (in_array($k, $key))
			{
				$query->where($query->quoteName($k) . '=' . $query->quote($v));

				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			if ($v === null)
			{
				// If the value is null and we want to update nulls then set it.
				if ($updateNulls)
				{
					$val = 'NULL';
				}
				// If the value is null and we do not want to update nulls then ignore this field.
				else
				{
					continue;
				}
			}
			else
			// The field is not null so we prep it for update.
			{
				$val = $query->quote($v);
			}

			// Add the field to be updated.
			$query->set($query->quoteName($k) . '=' . $val);
		}

		// Set the query and execute the update.
		$this->execute($query);
		
		return true;
	}

	/**
	 * save
	 *
	 * @param   string  $table        The name of the database table to update.
	 * @param   array   &$data        A reference to an object whose public properties match the table fields.
	 * @param   string  $key          The name of the primary key.
	 * @param   boolean $updateNulls  True to update null fields or false to ignore them.
	 *
	 * @return  bool|static
	 *
	 * @throws \InvalidArgumentException
	 */
	public function saveOne($table, &$data, $key, $updateNulls = false)
	{
		if (is_array($key) || is_object($key))
		{
			throw new \InvalidArgumentException(__NAMESPACE__ . '::save() dose not support multiple keys, please give me only one key.');
		}

		if (is_array($data))
		{
			$id = isset($data[$key]) ? $data[$key] : null;
		}
		else
		{
			$id = isset($data->$key) ? $data->$key : null;
		}

		if ($id)
		{
			return $this->updateOne($table, $data, $key, $updateNulls);
		}

		return $this->insertOne($table, $data, $key);
	}

	/**
	 * insertMultiple
	 *
	 * @param   string $table    The name of the database table to update.
	 * @param   array  &$dataSet A reference to an object whose public properties match the table fields.
	 * @param   array  $key      The name of the primary key.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function insertMultiple($table, &$dataSet, $key = null)
	{
		if (!is_array($dataSet) && !($dataSet instanceof \Traversable))
		{
			throw new \InvalidArgumentException('The data set to store should be array or \Traversable');
		}

		foreach ($dataSet as $k => $data)
		{
			$dataSet[$k] = $this->insertOne($table, $data, $key);
		}

		return $dataSet;
	}

	/**
	 * updateMultiple
	 *
	 * @param   string  $table       The name of the database table to update.
	 * @param   array   $dataSet     A reference to an object whose public properties match the table fields.
	 * @param   array   $key         The name of the primary key.
	 * @param   boolean $updateNulls True to update null fields or false to ignore them.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function updateMultiple($table, $dataSet, $key, $updateNulls = false)
	{
		if (!is_array($dataSet) && !($dataSet instanceof \Traversable))
		{
			throw new \InvalidArgumentException('The data set to store should be array or \Traversable');
		}

		foreach ($dataSet as $data)
		{
			$this->updateOne($table, $data, $key, $updateNulls);
		}

		return $dataSet;
	}

	/**
	 * saveMultiple
	 *
	 * @param   string  $table       The name of the database table to update.
	 * @param   array   $dataSet     A reference to an object whose public properties match the table fields.
	 * @param   array   $key         The name of the primary key.
	 * @param   boolean $updateNulls True to update null fields or false to ignore them.
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function saveMultiple($table, $dataSet, $key, $updateNulls = false)
	{
		if (!is_array($dataSet) && !($dataSet instanceof \Traversable))
		{
			throw new \InvalidArgumentException('The data set to store should be array or \Traversable');
		}

		foreach ($dataSet as $data)
		{
			$this->saveOne($table, $data, $key, $updateNulls);
		}

		return $dataSet;
	}

	/**
	 * Batch update some data.
	 *
	 * @param string $table      Table name.
	 * @param array  $data       Data you want to update.
	 * @param mixed  $conditions Where conditions, you can use array or Compare object.
	 *                           Example:
	 *                           - `array('id' => 5)` => id = 5
	 *                           - `new GteCompare('id', 20)` => 'id >= 20'
	 *                           - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 *
	 * @return  boolean True if update success.
	 */
	public function updateBatch($table, $data, $conditions = [])
	{
		$query = $this->db->getQuery(true);

		// Build conditions
		$query = QueryHelper::buildWheres($query, $conditions);

		// Build update values.
		$fields = $this->db->getTable($table)->getColumns();

		$hasField = false;

		foreach ((array) $data as $field => $value)
		{
			if (!in_array($field, $fields))
			{
				continue;
			}

			$query->set(QueryHelper::buildValueAssign($field, $value, $query));

			$hasField = true;
		}

		if (!$hasField)
		{
			return false;
		}

		$query->update($table);

		$this->execute($query);
		
		return true;
	}

	/**
	 * delete
	 *
	 * @param string $table
	 * @param array  $conditions
	 *
	 * @return  boolean
	 */
	public function delete($table, array $conditions = [])
	{
		$query = $this->db->getQuery(true);

		// Conditions.
		QueryHelper::buildWheres($query, $conditions);

		$query->delete($table);

		$this->db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 * Only applicable for DELETE, INSERT, or UPDATE statements.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   2.0
	 */
	public function countAffected()
	{
		// Get previous Reader to count affected
		return $this->db->getReader(null, true)->countAffected($this->getCursor());
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  string  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   2.0
	 */
	public function insertId()
	{
		return $this->db->getReader(null, true)->insertId();
	}

	/**
	 * execute
	 *
	 * @param   string|Query  $query
	 *
	 * @return  static
	 */
	public function execute($query)
	{
		$this->db->setQuery($query)->execute();

		$this->cursor = $this->db->getCursor();

		return $this;
	}

	/**
	 * Method to get property Db
	 *
	 * @return  \Windwalker\Database\Driver\AbstractDatabaseDriver
	 */
	public function getDriver()
	{
		return $this->db;
	}

	/**
	 * Method to set property db
	 *
	 * @param   \Windwalker\Database\Driver\AbstractDatabaseDriver $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDriver($db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Method to get property Cursor
	 *
	 * @return  resource
	 */
	public function getCursor()
	{
		return $this->cursor ? : $this->db->getCursor();
	}

	/**
	 * Method to set property cursor
	 *
	 * @param   resource $cursor
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setCursor($cursor)
	{
		$this->cursor = $cursor;

		return $this;
	}
}
