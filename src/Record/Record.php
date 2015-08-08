<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Record;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\DatabaseDriver;
use Windwalker\Query\Query;

/**
 * Class Record
 *
 * @since 2.0
 */
class Record implements \ArrayAccess, \IteratorAggregate
{
	const UPDATE_NULLS = true;

	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $table = '';

	/**
	 * The fields of the database table.
	 *
	 * @var    \stdClass
	 * @since  2.0
	 */
	protected $data = null;

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $keys = array();

	/**
	 * Indicates that the primary keys autoincrement.
	 *
	 * @var    boolean
	 * @since  2.0
	 */
	protected $autoIncrement = true;

	/**
	 * Property aliases.
	 *
	 * @var  array
	 */
	protected $aliases = array();

	/**
	 * Property fields.
	 *
	 * @var  array
	 */
	protected $fields = null;

	/**
	 * DatabaseDriver object.
	 *
	 * @var    DatabaseDriver
	 * @since  2.0
	 */
	protected $db;

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   string          $table  Name of the table to model.
	 * @param   mixed           $keys   Name of the primary key field in the table or array of field names that
	 *                                  compose the primary key.
	 * @param   DatabaseDriver  $db     DatabaseDriver object.
	 *
	 * @since   2.0
	 */
	public function __construct($table = null, $keys = 'id', DatabaseDriver $db = null)
	{
		$db = $db ? : DatabaseFactory::getDbo();

		// Set internal variables.
		$this->table = $table;
		$this->db    = $db;
		$this->data  = new \stdClass;

		// Set the key to be an array.
		if (is_string($keys))
		{
			$keys = array($keys);
		}
		elseif (is_object($keys))
		{
			$keys = (array) $keys;
		}

		$this->keys = $keys;

		$this->autoIncrement = (count($keys) == 1) ? true : false;

		// Initialise the table properties.
		$fields = $this->getFields();

		if ($fields)
		{
			foreach ($fields as $name => $v)
			{
				// Add the field if it is not already present.
				$this->data->$name = null;
			}
		}

		if (!$this->table)
		{
			throw new \InvalidArgumentException('Table name should not empty.');
		}
	}

	/**
	 * Magic setter to set a table field.
	 *
	 * @param   string  $key    The key name.
	 * @param   mixed   $value  The value to set.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Magic getter to get a table field.
	 *
	 * @param   string  $key  The key name.
	 *
	 * @return  mixed
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Magic setter to set a table field.
	 *
	 * @param   string  $key    The key name.
	 * @param   mixed   $value  The value to set.
	 *
	 * @return  static
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function set($key, $value)
	{
		$key = $this->resolveAlias($key);

		if (property_exists($this->data, $key))
		{
			$this->data->$key = $value;

			return $this;
		}
		else
		{
			throw new \InvalidArgumentException(__METHOD__ . ' - Set unknown property: ' . $key);
		}
	}

	/**
	 * Magic getter to get a table field.
	 *
	 * @param   string $key      The key name.
	 * @param   null   $default  The default value.
	 *
	 * @return  mixed
	 *
	 * @since   2.0
	 */
	public function get($key, $default = null)
	{
		$key = $this->resolveAlias($key);

		if (property_exists($this->data, $key))
		{
			return $this->data->$key;
		}

		return $default;
	}

	/**
	 * exists
	 *
	 * @param   string $key Is this key exists.
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function exists($key)
	{
		$key = $this->resolveAlias($key);

		return property_exists($this->data, $key);
	}

	/**
	 * Method to provide a shortcut to binding, checking and storing a AbstractTable
	 * instance to the database table.  The method will check a row in once the
	 * data has been stored and if an ordering filter is present will attempt to
	 * reorder the table rows based on the filter.  The ordering filter is an instance
	 * property name.  The rows that will be reordered are those whose value matches
	 * the AbstractTable instance for the property specified.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the AbstractTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 *                          to ignore while binding.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   2.0
	 */
	public function save($src, $ignore = '')
	{
		$this
			// Attempt to bind the source to the instance.
			->bind($src, $ignore)
			// Run any sanity checks on the instance and verify that it is ready for storage.
			->check()
			// Attempt to store the properties to the database table.
			->store();

		return $this;
	}

	/**
	 * Method to bind an associative array or object to the AbstractTable instance.  This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the AbstractTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  static  Method allows chaining
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			throw new \InvalidArgumentException(sprintf('%s::bind(*%s*)', get_class($this), gettype($src)));
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		$fields = $this->getFields();

		// Bind the source value, excluding the ignored fields.
		foreach ($src as $k => $v)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				$k = $this->resolveAlias($k);

				if (array_key_exists($k, $fields))
				{
					$this->data->$k = $v;
				}
			}
		}

		return $this;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the AbstractTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  static  Method allows chaining
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 * @throws  \InvalidArgumentException
	 */
	public function load($keys = null, $reset = true)
	{
		if (empty($keys))
		{
			$empty = true;
			$keys  = array();

			// If empty, use the value of the current key
			foreach ($this->keys as $key)
			{
				$empty      = $empty && empty($this->$key);
				$keys[$key] = $this->$key;
			}

			// If empty primary key there's is no need to load anything
			if ($empty)
			{
				return $this;
			}
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keyCount = count($this->keys);

			if ($keyCount)
			{
				if ($keyCount > 1)
				{
					throw new \InvalidArgumentException('Table has multiple primary keys specified, only one primary key value provided.');
				}

				$keys = array($this->getKeyName() => $keys);
			}
			else
			{
				throw new \RuntimeException('No table keys defined.');
			}
		}

		if ($reset)
		{
			$this->reset();
		}

		// Initialise the query.
		$query = $this->db->getQuery(true);
		$query->select('*');
		$query->from($this->db->quoteName($this->table));

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.

			if (isset($this->data->$field) || is_null($this->data->$field))
			{
				// Add the search tuple to the query.
				$query->where($this->db->quoteName($field) . ' = ' . $this->db->quote($value));
			}
			else
			{
				throw new \UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
			}
		}

		$this->db->setQuery($query);

		$row = $this->db->loadOne();

		// Check that we have a result.
		if (empty($row))
		{
			throw new \RuntimeException('No result.');
		}

		// Bind the object with the row and return.
		return $this->bind($row);
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pKey  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   2.0
	 * @throws  \UnexpectedValueException
	 */
	public function delete($pKey = null)
	{
		$key = $this->getKeyName();

		$pKey = (is_null($pKey)) ? $this->$key : $pKey;

		// If no primary key is given, return false.
		if ($pKey === null)
		{
			throw new \UnexpectedValueException('Null primary key not allowed.');
		}

		// Delete the row by primary key.
		$this->db->setQuery(
			$this->db->getQuery(true)
				->delete($this->db->quoteName($this->table))
				->where($this->db->quoteName($key) . ' = ' . $this->db->quote($pKey))
		)->execute();

		return $this;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @param bool $clear
	 *
	 * @return  static
	 *
	 * @since   2.0
	 */
	public function reset($clear = false)
	{
		// Get the default values for the class from the table.
		foreach ($this->getFields() as $k => $v)
		{
			$this->$k = $clear ? null : $v->Default;
		}

		return $this;
	}

	/**
	 * Method to perform sanity checks on the AbstractTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  static  Method allows chaining
	 *
	 * @since   2.0
	 */
	public function check()
	{
		return $this;
	}

	/**
	 * Method to store a row in the database from the AbstractTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * AbstractTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   2.0
	 */
	public function store($updateNulls = false)
	{
		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey())
		{
			$this->db->getWriter()->updateOne($this->table, $this->data, $this->keys, $updateNulls);
		}
		else
		{
			$this->db->getWriter()->insertOne($this->table, $this->data, $this->keys[0]);
		}

		return $this;
	}

	/**
	 * Validate that the primary key has been set.
	 *
	 * @return  boolean  True if the primary key(s) have been set.
	 *
	 * @since   2.0
	 */
	public function hasPrimaryKey()
	{
		if ($this->autoIncrement)
		{
			$empty = true;

			foreach ($this->keys as $key)
			{
				$empty = $empty && !$this->$key;
			}
		}
		else
		{
			$query = $this->db->getQuery(true);

			$query->select('COUNT(*)')
				->from($this->table);

			$this->appendPrimaryKeys($query);

			$this->db->setQuery($query);

			$count = $this->db->loadResult();

			if ($count == 1)
			{
				$empty = false;
			}
			else
			{
				$empty = true;
			}
		}

		return !$empty;
	}

	/**
	 * Method to append the primary keys for this table to a query.
	 *
	 * @param   Query  $query  A query object to append.
	 * @param   mixed  $pk     Optional primary key parameter.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   2.0
	 */
	public function appendPrimaryKeys(Query $query, $pk = null)
	{
		if (is_null($pk))
		{
			foreach ($this->keys as $k)
			{
				$query->where($this->db->quoteName($k) . ' = ' . $this->db->quote($this->$k));
			}
		}
		else
		{
			if (is_string($pk))
			{
				$pk = array($this->keys[0] => $pk);
			}

			$pk = (object) $pk;

			foreach ($this->keys AS $k)
			{
				$query->where($this->db->quoteName($k) . ' = ' . $this->db->quote($pk->$k));
			}
		}

		return $this;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @param   boolean  $multiple  True to return all primary keys (as an array) or false to return just the first one (as a string).
	 *
	 * @return  mixed  Array of primary key field names or string containing the first primary key field.
	 *
	 * @since   2.0
	 */
	public function getKeyName($multiple = false)
	{
		// Count the number of keys
		if (count($this->keys))
		{
			if ($multiple)
			{
				// If we want multiple keys, return the raw array.
				return $this->keys;
			}
			else
			{
				// If we want the standard method, just return the first key.
				return $this->keys[0];
			}
		}

		return '';
	}

	/**
	 * Get the columns from database table.
	 *
	 * @return  array  An array of the field names, or false if an error occurs.
	 *
	 * @since   2.0
	 * @throws  \UnexpectedValueException
	 */
	public function getFields()
	{
		if ($this->fields === null)
		{
			// Lookup the fields for this table only once.
			$fields = $this->db->getTable($this->table)->getColumnDetails(true);

			if (empty($fields))
			{
				throw new \UnexpectedValueException(sprintf('No columns found for %s table', $this->table));
			}

			$this->fields = $fields;
		}

		return $this->fields;
	}

	/**
	 * Method to check a field exists or not.
	 *
	 * @param string $name
	 *
	 * @return  boolean
	 */
	public function hasField($name)
	{
		return array_key_exists($name, (array) $this->fields);
	}

	/**
	 * Get the table name.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getTableName()
	{
		return $this->table;
	}

	/**
	 * Method to set property table
	 *
	 * @param   string $table
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setTableName($table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Get an iterator object.
	 *
	 * @return  \ArrayIterator
	 *
	 * @since   2.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * toObject
	 *
	 * @return  \stdClass
	 */
	public function toObject()
	{
		return $this->data;
	}

	/**
	 * toArray
	 *
	 * @return  array
	 */
	public function toArray()
	{
		return get_object_vars($this->data);
	}

	/**
	 * Clone the table.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function __clone()
	{
		$this->data = clone $this->data;
	}

	/**
	 * Quick quote.
	 *
	 * @param string $value
	 *
	 * @return  string
	 */
	public function q($value)
	{
		return $this->db->quote($value);
	}

	/**
	 * Quick quote name.
	 *
	 * @param string $value
	 *
	 * @return  mixed
	 */
	public function qn($value)
	{
		return $this->db->quoteName($value);
	}

	/**
	 * Check a field value exists in database or not, to keep a field unique.
	 *
	 * @param   string  $field  The field name to check.
	 *
	 * @return  boolean
	 */
	public function valueExists($field)
	{
		$record = new static($this->table, $this->keys, $this->db);

		$record->load(array($field => $this->$field));

		if ($record->$field != $this->$field)
		{
			return false;
		}

		// check record keys same as self
		$same = array();

		foreach ($this->keys as $key)
		{
			$same[] = ($record->$key == $this->$key);
		}

		// Key not same, means same value exists in other record.
		if (in_array(false, $same, true))
		{
			return true;
		}

		return false;
	}

	/**
	 * Set column alias.
	 *
	 * @param   string  $name
	 * @param   string  $alias
	 *
	 * @return  static
	 */
	public function setAlias($name, $alias)
	{
		if ($alias === null && isset($this->aliases[$name]))
		{
			unset($this->aliases[$name]);
		}
		else
		{
			$this->aliases[$name] = $alias;
		}

		return $this;
	}

	/**
	 * Resolve alias.
	 *
	 * @param   string $name
	 *
	 * @return  string
	 */
	public function resolveAlias($name)
	{
		if (isset($this->aliases[$name]))
		{
			return $this->aliases[$name];
		}

		return $name;
	}

	/**
	 * Is a property exists or not.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return $this->exists($offset);
	}

	/**
	 * Get a property.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  mixed The value to return.
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Set a value to property.
	 *
	 * @param mixed $offset Offset key.
	 * @param mixed $value  The value to set.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  void
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Unset a property.
	 *
	 * @param mixed $offset Offset key to unset.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  void
	 */
	public function offsetUnset($offset)
	{
		$this->data->$offset = null;
	}
}
