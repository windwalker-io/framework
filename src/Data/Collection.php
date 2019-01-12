<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Data;

use Windwalker\Data\Traits\CollectionTrait;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Iterator\ArrayObject;

/**
 * The Collection class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Collection extends ArrayObject implements DataInterface
{
    use CollectionTrait;

    /**
     * Bind the data into this object.
     *
     * @param   mixed   $values       The data array or object.
     * @param   boolean $replaceNulls Replace null or not.
     *
     * @return  static Return self to support chaining.
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
            $this->offsetSet($field, $value);
        }

        return $this;
    }

    /**
     * Is this object empty?
     *
     * @return  boolean
     */
    public function isNull()
    {
        return $this->storage === [];
    }

    /**
     * Is this object has properties?
     *
     * @return  boolean
     */
    public function notNull()
    {
        return $this->storage !== [];
    }

    /**
     * Dump all data as array
     *
     * @return  array
     */
    public function dump()
    {
        return $this->getArrayCopy();
    }

    /**
     * toCollections
     *
     * @param array $items
     *
     * @return  Collection[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function toCollections(array $items): array
    {
        foreach ($items as $k => $item) {
            $items[$k] = new static($item);
        }

        return $items;
    }

    /**
     * keys
     *
     * @param string|null $search
     * @param bool|null   $strict
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function keys(?string $search = null, ?bool $strict = null): array
    {
        if (func_get_args()['search'] ?? false) {
            return array_keys($this->storage, $search, (bool) $strict);
        }

        return array_keys($this->storage);
    }

    /**
     * chunk
     *
     * @param int  $num
     * @param bool $preserveKeys
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function chunk(int $num, ?bool $preserveKeys = null): self
    {
        return new static(static::toCollections(array_chunk($this->storage, $num, $preserveKeys)));
    }

    /**
     * column
     *
     * @param string      $name
     * @param string|null $key
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function column(string $name, ?string $key = null): array
    {
        return array_column($this->storage, $name, $key);
    }

    /**
     * combine
     *
     * @param array|static $values
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function combine($values): self
    {
        return new static(array_combine($this->storage, Arr::toArray($values)));
    }
}
