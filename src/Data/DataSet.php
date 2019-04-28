<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Data;

use Windwalker\Data\Traits\CollectionTrait;
use Windwalker\Utilities\Arr;

/**
 * The Data set to store multiple data.
 *
 * @since 2.0
 */
class DataSet implements
    DataSetInterface,
    \IteratorAggregate,
    \ArrayAccess,
    \Serializable,
    \Countable,
    \JsonSerializable
{
    use CollectionTrait;

    /**
     * The data store.
     *
     * @var  array
     */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        if ($data) {
            $this->bind($data);
        }
    }

    /**
     * Bind data array into self.
     *
     * @param array $dataset An array of multiple data.
     *
     * @throws \InvalidArgumentException
     * @return  DataSet Return self to support chaining.
     */
    public function bind($dataset)
    {
        if ($dataset === null) {
            return $this;
        }

        if ($dataset instanceof \Traversable) {
            $dataset = iterator_to_array($dataset);
        } elseif (is_object($dataset)) {
            $dataset = [$dataset];
        } elseif (!is_array($dataset)) {
            throw new \InvalidArgumentException('Need an array or object');
        }

        foreach ($dataset as $k => $data) {
            $this[$k] = $data;
        }

        return $this;
    }

    /**
     * The magic get method is used to get a list of properties from the objects in the data set.
     *
     * Example: $array = $dataSet->foo;
     *
     * This will return a column of the values of the 'foo' property in all the objects
     * (or values determined by custom property setters in the individual Data's).
     * The result array will contain an entry for each object in the list (compared to __call which may not).
     * The keys of the objects and the result array are maintained.
     *
     * @param   string $property The name of the data property.
     *
     * @return  array  An associative array of the values.
     */
    public function __get($property)
    {
        return $this->getColumn($property);
    }

    /**
     * The magic isset method is used to check the state of an object property using the iterator.
     *
     * Example: $array = isset($objectList->foo);
     *
     * @param   string $property The name of the property.
     *
     * @return  boolean  True if the property is set in any of the objects in the data set.
     */
    public function __isset($property)
    {
        $return = [];

        // Iterate through the objects.
        foreach ($this->data as $data) {
            // Check the property.
            $return[] = isset($data->$property);
        }

        return in_array(true, $return, true) ? true : false;
    }

    /**
     * The magic set method is used to set an object property using the iterator.
     *
     * Example: $objectList->foo = 'bar';
     *
     * This will set the 'foo' property to 'bar' in all of the objects
     * (or a value determined by custom property setters in the Data).
     *
     * @param   string $property The name of the property.
     * @param   mixed  $value    The value to give the data property.
     *
     * @return  void
     */
    public function __set($property, $value)
    {
        $this->setColumn($property, $value);
    }

    /**
     * The magic unset method is used to unset an object property using the iterator.
     *
     * Example: unset($objectList->foo);
     *
     * This will unset all of the 'foo' properties in the list of Data\Object's.
     *
     * @param   string $property The name of the property.
     *
     * @return  void
     */
    public function __unset($property)
    {
        // Iterate through the objects.
        foreach ($this->data as $data) {
            unset($data->$property);
        }
    }

    /**
     * Property is exist or not.
     *
     * @param mixed $offset Property key.
     *
     * @return  boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get a value of property.
     *
     * @param mixed $offset Property key.
     *
     * @return  mixed The value of this property.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Clears the objects in the data set.
     *
     * @return  DataSet  Returns itself to allow chaining.
     */
    public function clear()
    {
        $this->data = [];

        return $this;
    }

    /**
     * Set value to property
     *
     * @param mixed $offset Property key.
     * @param mixed $value  Property value to set.
     *
     * @return  void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset a property.
     *
     * @param mixed $offset Key to unset.
     *
     * @return  void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * get
     *
     * @param string $name
     *
     * @return  mixed|null
     */
    public function get($name)
    {
        if (empty($this->data[$name])) {
            return null;
        }

        return $this->data[$name];
    }

    /**
     * set
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return  static
     */
    public function set($name, $value)
    {
        if (!$value instanceof Data && !$value instanceof DataSet) {
            $value = new Data($value);
        }

        if ($name !== null) {
            $this->data[$name] = $value;
        } else {
            array_push($this->data, $value);
        }

        return $this;
    }

    public function getColumn($column)
    {
        $return = [];

        // Iterate through the objects.
        foreach ($this->data as $key => $data) {
            // Get the property.
            $return[$key] = $data->$column;
        }

        return $return;
    }

    /**
     * setColumn
     *
     * @param string $column
     * @param mixed  $value
     *
     * @return  static
     */
    public function setColumn($column, $value)
    {
        // Iterate through the objects.
        foreach ($this->data as $data) {
            // Set the property.
            $data->$column = $value;
        }

        return $this;
    }

    /**
     * Get the data store for iterate.
     *
     * @return  \Traversable The data to be iterator.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Serialize data.
     *
     * @return  string Serialized data string.
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * Unserialize the data.
     *
     * @param string $serialized THe serialized data string.
     *
     * @return  DataSet Support chaining.
     */
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);

        return $this;
    }

    /**
     * Count data.
     *
     * @return  int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Serialize to json format.
     *
     * @return  string Encoded json string.
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * Is this data set empty?
     *
     * @return  boolean Tru if empty.
     */
    public function isNull()
    {
        return empty($this->data);
    }

    /**
     * Is this data set has properties?
     *
     * @return  boolean True is exists.
     */
    public function notNull()
    {
        return !$this->isNull();
    }

    /**
     * Dump all data as array.
     *
     * @param bool $recursive
     *
     * @return Data[]
     */
    public function dump($recursive = false)
    {
        $dataset = $this->data;

        if ($recursive) {
            $dataset = static::allToArray($dataset);
        }

        return $dataset;
    }

    /**
     * Mapping all elements and return new instance.
     *
     * This method will rename to map() after 3.2.
     *
     * @param   callable $callback
     *
     * @return  static
     *
     * @since       3.1.3
     *
     * @deprecated  Use map() instead.
     */
    public function mapping($callback)
    {
        return $this->map($callback);
    }

    /**
     * mapColumn
     *
     * @param string   $field
     * @param callable $callback
     *
     * @return  static
     */
    public function mapColumn($field, callable $callback)
    {
        return $this->map(
            function (Data $data) use ($field, $callback) {
                $data->$field = $callback($data->$field);

                return $data;
            }
        );
    }

    /**
     * Mapping all elements.
     *
     * @param   callable $callback
     *
     * @return  static  Support chaining.
     *
     * @since   3.1.3
     */
    public function transform($callback)
    {
        return $this->walk($callback);
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
        array_walk($this->data, $callback, $userdata);

        return $this;
    }

    /**
     * Sort Dataset by key.
     *
     * @param   integer $flags You may modify the behavior of the sort using the optional parameter flags.
     *
     * @return  static  Support chaining.
     *
     * @since   2.0.9
     */
    public function ksort($flags = null)
    {
        ksort($this->data, (int) $flags);

        return $this;
    }

    /**
     * Sort DataSet by key in reverse order
     *
     * @param   integer $flags You may modify the behavior of the sort using the optional parameter flags.
     *
     * @return  static  Support chaining.
     *
     * @since   2.0.9
     */
    public function krsort($flags = null)
    {
        krsort($this->data, (int) $flags);

        return $this;
    }

    /**
     * Sort data.
     *
     * @param integer $flags You may modify the behavior of the sort using the optional parameter flags.
     *
     * @return  static  Support chaining.
     *
     * @since   3.0
     */
    public function sort($flags = null)
    {
        sort($this->data, $flags);

        return $this;
    }

    /**
     * Sort Data in reverse order.
     *
     * @param integer $flags You may modify the behavior of the sort using the optional parameter flags.
     *
     * @return  static  Support chaining.
     *
     * @since   3.0
     */
    public function rsort($flags = null)
    {
        rsort($this->data, $flags);

        return $this;
    }

    /**
     * Sort DataSet by keys using a user-defined comparison function
     *
     * @param   callable $callable The compare function used for the sort.
     *
     * @return  static  Support chaining.
     *
     * @since   2.0.9
     */
    public function uksort($callable)
    {
        uksort($this->data, $callable);

        return $this;
    }

    /**
     * Shuffle this DataSet to random orders.
     *
     * @return  static  Support chaining.
     *
     * @since   2.0.9
     */
    public function shuffle()
    {
        shuffle($this->data);

        return $this;
    }

    /**
     * Push element to last.
     *
     * @param   Data|mixed $data Data to push.
     *
     * @return  static
     */
    public function push($data)
    {
        $this[] = $data;

        return $this;
    }

    /**
     * Pop the last element.
     *
     * @return  Data
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * Shift the first element.
     *
     * @return  Data
     *
     * @since   3.0
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * Unshift the first element.
     *
     * @param   Data|mixed $data Data to push.
     *
     * @return  static
     *
     * @since   3.0
     */
    public function unshift($data)
    {
        array_unshift($this->data, $data);

        return $this;
    }

    /**
     * splice
     *
     * @param int   $offset
     * @param int   $length
     * @param mixed $replacement
     *
     * @return  static
     */
    public function splice($offset, $length = null, $replacement = null)
    {
        return $this->bindNewInstance(array_splice($this->data, $offset, $length, $replacement));
    }

    /**
     * sum
     *
     * @param string|int $field
     *
     * @return  float|int
     */
    public function sum($field)
    {
        return array_sum($this->$field);
    }

    /**
     * avg
     *
     * @param string|int $field
     *
     * @return  float|int
     */
    public function avg($field)
    {
        return $this->sum($field) / count($this->data);
    }

    /**
     * contains
     *
     * @param string $field
     * @param mixed  $value
     * @param bool   $strict
     *
     * @return  bool
     */
    public function contains($field, $value, $strict = false)
    {
        return in_array($value, $this->$field, $strict);
    }

    /**
     * containsAll
     *
     * @param mixed $value
     * @param bool  $strict
     *
     * @return  bool
     */
    public function containsAll($value, $strict = false)
    {
        return in_array($value, Arr::collapse($this->dump(true)), $strict);
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

        return $this->map(
            static function (Data $data) use ($fields) {
                return $data->except($fields);
            }
        );
    }

    /**
     * only
     *
     * @param array|string $fields
     *
     * @return  static
     */
    public function only($fields)
    {
        $fields = (array) $fields;

        return $this->map(
            static function (Data $data) use ($fields) {
                return $data->only($fields);
            }
        );
    }

    /**
     * Clone this class.
     *
     * @return  void
     *
     * @since   2.0.9
     */
    public function __clone()
    {
        $data = [];

        foreach ($this->data as $item) {
            if (is_object($item)) {
                $data[] = clone $item;
            } else {
                $data[] = $item;
            }
        }

        $this->data = $data;
    }

    /**
     * Return all the keys of this DataSet.
     *
     * @return  array
     *
     * @since       2.0.9
     *
     * @deprecated  Use keys().
     */
    public function getKeys()
    {
        return $this->keys();
    }

    /**
     * Return all the keys of this DataSet.
     *
     * @return  array
     *
     * @since   3.2
     */
    public function keys()
    {
        return array_keys($this->data);
    }
}
