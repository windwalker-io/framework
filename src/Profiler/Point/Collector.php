<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Profiler\Point;

/**
 * The Collector class.
 *
 * @since  2.1.1
 */
class Collector implements CollectorInterface, \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Property data.
     *
     * @var  array
     */
    protected $data = [];

    /**
     * Class init.
     *
     * @param  array $data
     */
    public function __construct($data = [])
    {
        $this->data = (array)$data;
    }

    /**
     * Get a value.
     *
     * @param   string $name    The data name you want to get.
     * @param   mixed  $default The default value if not exists.
     *
     * @return  mixed  The found value or default.
     */
    public function get($name, $default = null)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $default;
    }

    /**
     * set
     *
     * @param   string $name
     * @param   mixed  $value
     *
     * @return  static
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Get all data.
     *
     * @return  array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * setData
     *
     * @param array $data
     *
     * @return  static
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Count the number of arguments.
     *
     * @return  integer  The number of arguments.
     *
     * @since   2.0
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Tell if the given event argument exists.
     *
     * @param   string $name The argument name.
     *
     * @return  boolean  True if it exists, false otherwise.
     *
     * @since   2.0
     */
    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Get an event argument value.
     *
     * @param   string $name The argument name.
     *
     * @return  mixed  The argument value or null if not existing.
     *
     * @since   2.0
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Set the value of an event argument.
     *
     * @param   string $name  The argument name.
     * @param   mixed  $value The argument value.
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException  If the argument name is null.
     *
     * @since   2.0
     */
    public function offsetSet($name, $value)
    {
        if (is_null($name)) {
            throw new \InvalidArgumentException('The key name cannot be null.');
        }

        $this->set($name, $value);
    }

    /**
     * Remove an event argument.
     *
     * @param   string $name The argument name.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function offsetUnset($name)
    {
        if ($this[$name]) {
            unset($this->data[$name]);
        }
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Traversable An instance of an object implementing Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}
