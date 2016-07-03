<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper\Entity;

use Windwalker\Data\Data;

/**
 * Entity is a Data object sub class, we can set fields of this object
 * then help us filter non necessary values to prevent error when inserting to database.
 */
class Entity extends Data
{
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
	 * @param array $fields
	 * @param mixed $data
	 */
	public function __construct($fields = null, $data = null)
	{
		if (is_array($fields))
		{
			$this->addFields($fields);
		}

		$this->reset();

		parent::__construct($data);

		$this->init();
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
	 * loadFields
	 *
	 * @return  \stdClass[]
	 */
	public function getFields()
	{
		return $this->fields;
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

		$field = (object) $field;

		$this->fields[$field->Field] = $field;

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
	 * Remove field from this entity.
	 *
	 * @param string $field Field name.
	 *
	 * @return  Entity Return self to support chaining.
	 */
	public function removeField($field)
	{
		$field = $this->resolveAlias($field);
		
		unset($this->fields[$field]);
		unset($this->data[$field]);

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
	 * @param bool $all
	 *
	 * @return \ArrayIterator
	 * @since   2.0
	 */
	public function getIterator($all = false)
	{
		return new \ArrayIterator($this->dump($all));
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
	 * Set value.
	 *
	 * @param string $field The field to set.
	 * @param mixed  $value The value to set.
	 *
	 * @return  void
	 */
	public function __set($field, $value = null)
	{
		$this->set($field, $value);
	}

	/**
	 * Get value.
	 *
	 * @param string $field The field to get.
	 *
	 * @return  mixed The value we want ot get.
	 */
	public function __get($field)
	{
		return $this->get($field);
	}

	/**
	 * __unset
	 *
	 * @param   string  $name
	 *
	 * @return  void
	 */
	public function __unset($name)
	{
		$this->set($name, null);
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
	public function set($key, $value = null)
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
	 * Is a property exists or not.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return $this->hasField($offset);
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
		$this->data[$offset] = null;
	}

	/**
	 * Count this object.
	 *
	 * @return  int
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Method to check a field exists.
	 *
	 * @param string $field The field name to check.
	 *
	 * @return  boolean True if exists.
	 */
	public function exists($field)
	{
		return $this->hasField($field);
	}

	/**
	 * Is this object empty?
	 *
	 * @return  boolean
	 */
	public function isNull()
	{
		foreach ($this->data as $value)
		{
			if ($value !== null)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Dump all data as array
	 *
	 * @param bool $all
	 *
	 * @return array
	 */
	public function dump($all = false)
	{
		if ($all)
		{
			return $this->data;
		}

		$data = array();

		foreach (array_keys($this->getFields()) as $field)
		{
			$data[$field] = $this->data[$field];
		}

		return $data;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return  static
	 *
	 * @since   2.0
	 */
	public function reset()
	{
		$this->data = array();

		// Get the default values for the class from the table.
		foreach ((array) $this->getFields() as $k => $v)
		{
			$this->data[$k] = null;
		}

		return $this;
	}
}
