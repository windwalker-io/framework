<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper\Entity;

use Windwalker\Data\Data;
use Windwalker\DataMapper\Adapter\WindwalkerAdapter;

/**
 * Entity is a Data object sub class, we can set fields of this object
 * then help us filter non necessary values to prevent error when inserting to database.
 */
class Entity extends Data
{
	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 *
	 * @since  3.0
	 */
	protected $table = '';

	/**
	 * Property data.
	 *
	 * @var  array
	 */
	protected $data = array();

	/**
	 * Property aliases.
	 *
	 * @var  array
	 */
	protected $aliases = array();

	/**
	 * Property fields.
	 *
	 * @var  \stdClass[]
	 */
	protected $fields = null;

	/**
	 * Constructor.
	 *
	 * @param string $table
	 * @param array  $fields
	 * @param mixed  $data
	 */
	public function __construct($table = null, $fields = null, $data = null)
	{
		$this->table = $table;

		if ($fields === null)
		{
			$fields = $this->loadFields($table);
		}

		if ($fields)
		{
			$this->addFields($fields);
		}

		$this->reset();

		parent::__construct($data);

		$this->init();
	}

	/**
	 * loadFields
	 *
	 * @param   string  $table
	 *
	 * @return  \stdClass[]
	 */
	public function loadFields($table = null)
	{
		$table = $table ? : $this->table;

		if ($table)
		{
			return WindwalkerAdapter::getInstance()->getColumnDetails($table);
		}

		return array();
	}

	/**
	 * Prepare your logic.
	 *
	 * @return  void
	 */
	protected function init()
	{
		// Override this method if you need.
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
	 * Add a field to this entity.
	 *
	 * @param array $fields Fields array.
	 *
	 * @return  Entity Return self to support chaining.
	 */
	public function addFields($fields)
	{
		foreach ($fields as $field)
		{
			$this->addField($field);
		}

		return $this;
	}

	/**
	 * Add a field.
	 *
	 * @param string  $field    Field name.
	 * @param mixed   $default  The default value of this field.
	 *
	 * @return Entity Return self to support chaining.
	 */
	public function addField($field, $default = null)
	{
		if ($default !== null && (is_array($default) || is_object($default)))
		{
			throw new \InvalidArgumentException(sprintf('Default value should be scalar, %s given.', gettype($default)));
		}
		
		$defaultProfile = array(
			'Name'      => '',
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
			$field = (object) array_merge($defaultProfile, array(
				'Name'    => $field,
				'Type'    => gettype($default),
				'Default' => $default
			));
		}

		if (is_array($field) || is_object($field))
		{
			$field = array_merge($defaultProfile, (array) $field);
		}

		if ($field['Null'] == 'No' && $field['Default'] === null)
		{
			$field['Default'] = '';
		}

		$this->fields[$field] = $field;

		return $this;
	}

	/**
	 * Remove field from this entity.
	 *
	 * @param string $field Field name.
	 *
	 * @return  Entity Return self to support chaining.
	 */
	public function removeField($field)
	{
		unset($this->fields[$field]);

		return $this;
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
		$name = $this->resolveAlias($name);

		return array_key_exists($name, (array) $this->fields);
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
		return (object) $this->data;
	}

	/**
	 * toArray
	 *
	 * @return  array
	 */
	public function toArray()
	{
		return $this->data;
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
	 * __isset
	 *
	 * @param   string  $name
	 *
	 * @return  boolean
	 */
	public function __isset($name)
	{
		return $this->hasField($name);
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

		$this->data[$key] = $value;

		return $this;
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

		if (isset($this->data[$key]))
		{
			return $this->data[$key];
		}

		return $default;
	}

	/**
	 * reset
	 *
	 * @param bool $useDefault
	 *
	 * @return  static
	 */
	public function reset($useDefault = true)
	{
		foreach ($this->data as $key => $value)
		{
			$default = null;

			if ($useDefault && isset($this->fields[$key]->Default))
			{
				$default = $this->fields[$key]->Default;
			}

			$this->data[$key] = $default;
		}

		return $this;
	}
}
