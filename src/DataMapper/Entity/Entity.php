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
class Entity extends Data implements \JsonSerializable
{
    const DUMP_ALL_DATA = true;

    /**
     * Property data.
     *
     * @var  array
     */
    protected $data = [];

    /**
     * Property aliases.
     *
     * @var  array
     */
    protected $aliases = [];

    /**
     * Property fields.
     *
     * @var  \stdClass[]
     */
    protected $fields = null;

    /**
     * Property casts.
     *
     * @var  array
     */
    protected $casts = [];

    /**
     * Constructor.
     *
     * @param array $fields
     * @param mixed $data
     */
    public function __construct($fields = null, $data = null)
    {
        if (is_array($fields)) {
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
        if ($default !== null && (is_array($default) || is_object($default))) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Default value should be scalar, %s given.',
                    gettype($default)
                )
            );
        }

        $defaultProfile = [
            'Field' => '',
            'Type' => '',
            'Collation' => 'utf8_unicode_ci',
            'Null' => 'NO',
            'Key' => '',
            'Default' => '',
            'Extra' => '',
            'Privileges' => 'select,insert,update,references',
            'Comment' => '',
        ];

        if (is_string($field)) {
            $field = array_merge(
                $defaultProfile,
                [
                    'Field' => $field,
                    'Type' => gettype($default),
                    'Default' => $default,
                ]
            );
        }

        if (is_array($field) || is_object($field)) {
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
        foreach ($fields as $field) {
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

        unset($this->fields[$field], $this->data[$field]);

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
     * @param   string $name
     * @param   string $alias
     *
     * @return  static
     */
    public function setAlias($name, $alias)
    {
        if ($alias === null && isset($this->aliases[$name])) {
            unset($this->aliases[$name]);
        } else {
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
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        return $name;
    }

    /**
     * __isset
     *
     * @param   string $name
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
     * @throws \InvalidArgumentException
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
     * @param   string $name
     *
     * @return  void
     * @throws \InvalidArgumentException
     */
    public function __unset($name)
    {
        $this->set($name, null);
    }

    /**
     * Magic setter to set a table field.
     *
     * @param   string $key   The key name.
     * @param   mixed  $value The value to set.
     *
     * @return  static
     *
     * @since   2.0
     * @throws  \InvalidArgumentException
     */
    public function set($key, $value = null)
    {
        $key = $this->resolveAlias($key);

        $mutator = 'set' . $this->toCamelCase($key) . 'Value';

        if (is_callable([$this, $mutator])) {
            $this->$mutator($value);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Magic getter to get a table field.
     *
     * @param   string $key     The key name.
     * @param   null   $default The default value.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function get($key, $default = null)
    {
        $key = $this->resolveAlias($key);

        $accessor = 'get' . $this->toCamelCase($key) . 'Value';

        $value = isset($this->data[$key]) ? $this->data[$key] : null;

        if (is_callable([$this, $accessor])) {
            return $this->$accessor($value);
        }

        if ($cast = $this->getCast($key)) {
            $value = $this->castValue($key, $value);
        }

        if ($value === null) {
            return $default;
        }

        return $value;
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
        foreach ($this->data as $value) {
            if ($value !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is this object has properties?
     *
     * @return  boolean
     */
    public function notNull()
    {
        return !$this->isNull();
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
        if ($all) {
            return $this->data;
        }

        $data = [];

        foreach (array_keys($this->getFields()) as $field) {
            $data[$field] = $this->data[$field];
        }

        return $data;
    }

    /**
     * toArray
     *
     * @param bool $all
     *
     * @return  array
     */
    public function toArray($all)
    {
        $keys = $all ? array_keys($this->data) : array_keys($this->getFields());

        $data = [];

        foreach ($keys as $field) {
            $data[$field] = $this->get($field);
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
        $this->data = [];

        // Get the default values for the class from the table.
        foreach ((array) $this->getFields() as $k => $v) {
            $this->data[$k] = null;
        }

        return $this;
    }

    /**
     * toCamelCase
     *
     * @param  string $input
     *
     * @return  string
     */
    protected function toCamelCase($input)
    {
        $input = str_replace('_', ' ', $input);
        $input = ucwords($input);

        return str_ireplace(' ', '', $input);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return  mixed data which can be serialized by json_encode(),
     *          which is a value of any type other than a resource.
     *
     * @since   3.1.3
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * setCast
     *
     * @param string $field
     * @param string $cast
     *
     * @return  static
     */
    public function setCast($field, $cast)
    {
        $this->casts[$field] = $cast;

        return $this;
    }

    /**
     * getCast
     *
     * @param string $field
     *
     * @return  string
     */
    public function getCast($field)
    {
        if (isset($this->casts[$field])) {
            return $this->casts[$field];
        }

        return null;
    }

    /**
     * castValue
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return  mixed
     */
    public function castValue($field, $value)
    {
        if (null === $value) {
            return $value;
        }

        $cast = $this->getCast($field);

        switch ($cast) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return json_decode($value);
            case 'array':
            case 'json':
                return json_decode($value, true);
            case 'date':
            case 'datetime':
                return $this->toDateTime($value);
            case 'timestamp':
                return $this->toDateTime($value)->getTimestamp();
            case class_exists($cast):
                return new $cast($value);
            case is_callable($cast):
                return $cast($value);
            default:
                return $value;
        }
    }

    /**
     * toDateTime
     *
     * @param string $date
     *
     * @return  bool|\DateTime
     */
    public function toDateTime($date)
    {
        if ($date instanceof \DateTimeInterface) {
            return $date;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date)) {
            return \DateTime::createFromFormat('Y-m-d', $date);
        }

        return \DateTime::createFromFormat($this->db->getQuery(true)->getDateFormat(), $date);
    }
}
