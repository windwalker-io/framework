<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Data;

use Windwalker\Data\Traits\CollectionTrait;

/**
 * Data object to store values.
 *
 * @since 2.0
 */
#[\AllowDynamicProperties]
class Data implements DataInterface, \IteratorAggregate, \ArrayAccess, \Countable, \JsonSerializable
{
    use CollectionTrait;

    /**
     * Constructor.
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->bind($data);
        }
    }

    /**
     * Bind the data into this object.
     *
     * @param   mixed   $values       The data array or object.
     * @param   boolean $replaceNulls Replace null or not.
     *
     * @return  static Return self to support chaining.
     *
     * @throws \InvalidArgumentException
     */
    public function bind($values, $replaceNulls = false)
    {
        if ($values === null) {
            return $this;
        }

        // Check properties type.
        if (!is_array($values) && !is_object($values)) {
            throw new \InvalidArgumentException(sprintf('Please bind array or object, %s given.', gettype($values)));
        }

        // If is Traversable, get iterator.
        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        } elseif (is_object($values)) {
            // If is object, convert it to array
            $values = get_object_vars($values);
        }

        // Bind the properties.
        foreach ($values as $field => $value) {
            // Check if the value is null and should be bound.
            if ($value === null && !$replaceNulls) {
                continue;
            }

            // Set the property.
            $this->set($field, $value);
        }

        return $this;
    }

    /**
     * Set value to Data object.
     *
     * @param string $field The field to set.
     * @param mixed  $value The value to set.
     *
     * @note  If you get "Cannot access property started with '\0'" error message, means you should not
     *        use (array) to convert object to array. This action will make protected property contains in array
     *        and start with \0 of property name. Use `get_object_vars()` instead.
     *
     * @throws  \InvalidArgumentException
     * @return  static Return self to support chaining.
     */
    public function set($field, $value = null)
    {
        if ($field === null) {
            throw new \InvalidArgumentException('Cannot access empty property');
        }

        $this->$field = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @param string $field   The field to get.
     * @param mixed  $default The default value if not exists.
     *
     * @throws  \InvalidArgumentException
     * @return  mixed The value we want ot get.
     */
    public function get($field, $default = null)
    {
        if (isset($this->$field)) {
            return $this->$field;
        }

        return $default;
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
        // Remove \0 from begin of field name.
        if (strpos((string) $field, "\0") === 0) {
            $field = substr($field, 3);
        }

        return isset($this->$field);
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
     * __isset
     *
     * @param   string $field
     *
     * @return  boolean
     */
    public function __isset($field)
    {
        return isset($this->$field);
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
     */
    public function __unset($name)
    {
        unset($this->$name);
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Traversable An instance of an object implementing Iterator or Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator(get_object_vars($this));
    }

    /**
     * Is a property exists or not.
     *
     * @param mixed $offset Offset key.
     *
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        // Remove \0 from begin of field name.
        if (strpos((string) $offset, "\0") === 0) {
            $offset = substr($offset, 3);
        }

        unset($this->$offset);
    }

    /**
     * Count this object.
     *
     * @return  int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count(get_object_vars($this));
    }

    /**
     * Is this object empty?
     *
     * @return  boolean
     */
    public function isNull()
    {
        return !$this->notNull();
    }

    /**
     * Is this object has properties?
     *
     * @return  boolean
     */
    public function notNull()
    {
        return (bool) count($this);
    }

    /**
     * Dump all data as array
     *
     * @return  array
     */
    public function dump()
    {
        return get_object_vars($this);
    }

    /**
     * Mapping all elements and return new instance.
     *
     * @param   callable $callback Callback to handle every element.
     *
     * @return  static  Support chaining.
     *
     * @since   3.1.3
     */
    public function mapping($callback)
    {
        return $this->map($callback);
    }

    /**
     * Apply a user supplied function to every member of this object.
     *
     * @param   callable $callback Callback to handle every element.
     * @param   mixed    $userdata This will be passed as the third parameter to the callback.
     *
     * @return  static  Support chaining.
     *
     * @since   2.0.9
     */
    public function walk($callback, $userdata = null)
    {
        foreach ($this->getIterator() as $key => $value) {
            call_user_func_array($callback, [&$value, $key, $userdata]);

            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * keys
     *
     * @return  array
     */
    public function keys()
    {
        return array_keys($this->dump());
    }

    /**
     * diff
     *
     * @param array|DataInterface $array
     *
     * @return  array
     */
    public function diff($array)
    {
        $self = $this->dump();

        return array_diff($self, $this->convertArray($array));
    }

    /**
     * diffKeys
     *
     * @param array|DataInterface $array
     *
     * @return  array
     */
    public function diffKeys($array)
    {
        $self = $this->dump();

        return array_diff_key($self, $this->convertArray($array));
    }

    /**
     * intersect
     *
     * @param array|DataInterface $array
     *
     * @return  array
     */
    public function intersect($array)
    {
        $self = $this->dump();

        return array_intersect($self, $this->convertArray($array));
    }

    /**
     * intersectKeys
     *
     * @param array|DataInterface $array
     *
     * @return  array
     */
    public function intersectKeys($array)
    {
        $self = $this->dump();

        return array_intersect_key($self, $this->convertArray($array));
    }

    /**
     * remove
     *
     * @param array|string $fields
     *
     * @return  static
     */
    public function except($fields)
    {
        $fields = (array) $fields;

        $new = clone $this;

        foreach ($fields as $field) {
            unset($new->$field);
        }

        return $new;
    }

    /**
     * only
     *
     * @param array|string $fields
     *
     *
     * @return  static
     */
    public function only($fields)
    {
        $fields = (array) $fields;

        $new = $this->getNewInstance();

        foreach ($fields as $origin => $field) {
            if (is_numeric($origin)) {
                $new->$field = $this->$field;
            } else {
                $new->$field = $this->$origin;
            }
        }

        return $new;
    }

    /**
     * sum
     *
     * @return  float|int
     */
    public function sum()
    {
        return array_sum($this->dump());
    }

    /**
     * avg
     *
     * @return  float|int
     */
    public function avg()
    {
        return $this->sum() / count($this);
    }

    /**
     * contains
     *
     * @param mixed $value
     * @param bool  $strict
     *
     * @return  bool
     */
    public function contains($value, $strict = false)
    {
        return in_array($value, $this->dump(), $strict);
    }

    /**
     * toCollection
     *
     * @return  Collection
     *
     * @since  3.5.5
     */
    public function toCollection(): Collection
    {
        return new Collection($this->dump());
    }

    /**
     * Clone this object.
     *
     * @return  void
     *
     * @since   2.0.9
     */
    public function __clone()
    {
        foreach ($this as $key => $item) {
            if (is_object($item)) {
                $this->$key = clone $item;
            }
        }
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
