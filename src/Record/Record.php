<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Record;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Database\Schema\DataType;
use Windwalker\DataMapper\Entity\Entity;
use Windwalker\Event\Dispatcher;
use Windwalker\Event\DispatcherInterface;
use Windwalker\Event\Event;
use Windwalker\Event\ListenerMapper;
use Windwalker\Query\Query;
use Windwalker\Record\Exception\NoResultException;

/**
 * Class Record
 *
 * @since 2.0
 */
class Record extends Entity
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
	 * Property data.
	 *
	 * @var  array
	 */
	protected $data = array();

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
	protected $autoIncrement = null;

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
	 * @var    AbstractDatabaseDriver
	 * @since  2.0
	 */
	protected $db;

	/**
	 * Property dispatcher.
	 *
	 * @var  Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Property fieldsCache.
	 *
	 * @var  array
	 */
	protected static $fieldsCache = array();

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   string          $table  Name of the table to model.
	 * @param   mixed           $keys   Name of the primary key field in the table or array of field names that
	 *                                  compose the primary key.
	 * @param   AbstractDatabaseDriver  $db     DatabaseDriver object.
	 *
	 * @since   2.0
	 */
	public function __construct($table = null, $keys = 'id', AbstractDatabaseDriver $db = null)
	{
		$db = $db ? : DatabaseFactory::getDbo();

		// Set internal variables.
		$this->table = $this->table ? : $table;
		$this->db    = $db;

		if (!$this->keys)
		{
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
		}

		if ($this->autoIncrement === null)
		{
			$this->autoIncrement = (count($keys) == 1) ? true : false;
		}

		// Initialise the table properties.
		$this->reset();

		if (!$this->table)
		{
			throw new \InvalidArgumentException('Table name should not empty.');
		}
	}

	/**
	 * Method to provide a shortcut to binding, checking and storing a AbstractTable
	 * instance to the database table.  The method will check a row in once the
	 * data has been stored and if an ordering filter is present will attempt to
	 * reorder the table rows based on the filter.  The ordering filter is an instance
	 * property name.  The rows that will be reordered are those whose value matches
	 * the AbstractTable instance for the property specified.
	 *
	 * @param   mixed    $src          An associative array or object to bind to the AbstractTable instance.
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   2.0
	 */
	public function save($src, $updateNulls = false)
	{
		return $this
			// Attempt to bind the source to the instance.
			->bind($src)
			// Run any sanity checks on the instance and verify that it is ready for storage.
			->validate()
			// Attempt to store the properties to the database table.
			->store($updateNulls);
	}

	/**
	 * Method to bind an associative array or object to the AbstractTable instance.  This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed $src           An associative array or object to bind to the AbstractTable instance.
	 * @param   bool  $replaceNulls  Replace NULL value.
	 *
	 * @return static Method allows chaining
	 *
	 * @since   2.0
	 */
	public function bind($src, $replaceNulls = false)
	{
		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			throw new \InvalidArgumentException(sprintf('%s::bind(*%s*)', get_class($this), gettype($src)));
		}

		if ($src instanceof \Traversable)
		{
			$src = iterator_to_array($src);
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		$fields = $this->loadFields();

		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'src'    => &$src,
			'fields' => $fields,
			'replaceNulls' => &$replaceNulls
		));

		// Bind the source value, excluding the ignored fields.
		foreach ($src as $k => $v)
		{
			if ($v === null && !$replaceNulls)
			{
				continue;
			}

			// Only process values in fields
			$k = $this->resolveAlias($k);

			if (array_key_exists($k, $fields))
			{
				$this->data[$k] = $v;
			}
		}

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

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

		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'conditions'  => &$keys,
			'reset' => &$reset
		));

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

			if (isset($this->data[$field]) || is_null($this->data[$field]))
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
			throw new NoResultException('No result.');
		}

		// Bind the object with the row and return.
		$row = $this->bind($row);

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__), array(
			'result' => &$row,
		));

		return $row;
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

		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'conditions'  => &$pKey
		));

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

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

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
	 *
	 * @throws  \RuntimeException
	 */
	public function validate()
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
	 * @return  static  Method allows chaining
	 *
	 * @since   2.0
	 */
	public function store($updateNulls = false)
	{
		// Event
		$this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), array(
			'updateNulls' => &$updateNulls
		));

		// Filter non-necessary field
		$data = array();

		$update = (bool) $this->hasPrimaryKey();

		/*
		 * If is create, or update with null values, we must check all NOT NULL
		 * fields uses default value instead NULL value. This helps us get rid of
		 * this Mysql warning in STRICT_TRANS_TABLE mode.
		 */
		if (!$update || $updateNulls)
		{
			foreach ($this->loadFields() as $field => $detail)
			{
				$data[$field] = $this->$field;

				// This field is null and the db column is not nullable, use db default value.
				if ($data[$field] === null && strtolower($detail->Null) == 'no')
				{
					$data[$field] = $detail->Default;
				}
			}
		}
		// Otherwise we just send current data
		else
		{
			$data = (array) $this->data;
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($update)
		{
			$this->db->getWriter()->updateOne($this->table, $data, $this->keys, $updateNulls);
		}
		else
		{
			$this->db->getWriter()->insertOne($this->table, $data, $this->keys[0]);
		}

		$this->data[$this->keys[0]] = $data[$this->keys[0]];

		// Event
		$this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

		return $this;
	}

	/**
	 * addField
	 *
	 * @param  string $field
	 * @param  string $default
	 *
	 * @return  static
	 */
	public function addField($field, $default = null)
	{
		if ($default !== null && (is_array($default) || is_object($default)))
		{
			throw new \InvalidArgumentException(sprintf('Default value should be scalar, %s given.', gettype($default)));
		}

		$defaultProfile = array(
			'Field'      => '',
			'Type'      => '',
			'Collation' => 'utf8_unicode_ci',
			'Null'      => 'NO',
			'Key'       => '',
			'Default'   => '',
			'Extra'     => '',
			'Privileges' => 'select,insert,update,references',
			'Comment'    => ''
		);

		if (is_string($field))
		{
			$field = array_merge($defaultProfile, array(
				'Field'    => $field,
				'Type'    => gettype($default),
				'Default' => $default
			));
		}

		if (is_array($field) || is_object($field))
		{
			$field = array_merge($defaultProfile, (array) $field);
		}

		if (strtolower($field['Null']) == 'no' && $field['Default'] === null
			&& $field['Key'] != 'PRI' && $this->getKeyName() != $field['Field'])
		{
			$type = $field['Type'];

			list($type,) = explode('(', $type, 2);
			$type = strtolower($type);

			$typeMapper = DataType::getInstance($this->db->getName());

			$field['Default'] = $typeMapper->getDefaultValue($type);
		}

		$field = (object) $field;

		$this->fields[$field->Field] = $field;

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
			return parent::hasPrimaryKey();
		}

		$query = $this->db->getQuery(true);

		$query->select('COUNT(*)')
			->from($this->table);

		$this->appendPrimaryKeys($query);

		$this->db->setQuery($query);

		$count = $this->db->loadResult();

		return $count != 1;
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
			if (!is_array($pk) && !is_object($pk))
			{
				if (count($this->keys))
				{
					throw new \InvalidArgumentException(sprintf(
						'%s has %s keys, please do not send only one value.',
						__CLASS__,
						count($this->keys)
					));
				}

				$pk = array($this->keys[0] => $pk);
			}

			$pk = (array) $pk;

			foreach ($this->keys AS $k)
			{
				$query->where($this->db->quoteName($k) . ' = ' . $this->db->quote($pk[$k]));
			}
		}

		return $this;
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
	 * @param   string $field The field name to check.
	 *
	 * @param null     $value
	 *
	 * @return bool
	 */
	public function valueExists($field, $value = null)
	{
		$record = new static($this->table, $this->keys, $this->db);
		
		if ($value === null)
		{
			$value = $this->$field;
		}

		$record->load(array($field => $value));

		if ($record->$field != $value)
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
	 * triggerEvent
	 *
	 * @param   string|Event  $event
	 * @param   array         $args
	 *
	 * @return  Event
	 *
	 * @since   2.1
	 */
	public function triggerEvent($event, $args = array())
	{
		$dispatcher = $this->getDispatcher();

		if (!$dispatcher instanceof DispatcherInterface)
		{
			return null;
		}

		$args['record'] = $this;

		$event = $this->dispatcher->triggerEvent($event, $args);

		$innerListener = array($this, $event->getName());

		if (!$event->isStopped() && is_callable($innerListener))
		{
			call_user_func($innerListener, $event);
		}

		return $event;
	}

	/**
	 * Method to get property Dispatcher
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   2.1
	 */
	public function getDispatcher()
	{
		if (!$this->dispatcher && class_exists('Windwalker\Event\Dispatcher'))
		{
			$this->dispatcher = new Dispatcher;

			if (is_subclass_of($this, 'Windwalker\Evebt\DispatcherAwareInterface'))
			{
				ListenerMapper::add($this);
			}
		}

		return $this->dispatcher;
	}

	/**
	 * Method to set property dispatcher
	 *
	 * @param   DispatcherInterface $dispatcher
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   2.1
	 */
	public function setDispatcher(DispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;

		return $this;
	}

	/**
	 * Method to get property Db
	 *
	 * @return  AbstractDatabaseDriver
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Method to set property db
	 *
	 * @param   AbstractDatabaseDriver $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}
}
