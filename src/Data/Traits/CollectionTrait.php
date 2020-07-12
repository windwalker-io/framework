<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Data\Traits;

use Windwalker\Data\Collection;
use Windwalker\Data\Data;
use Windwalker\Data\DataInterface;
use Windwalker\Data\DataSet;
use Windwalker\Data\DataSetInterface;
use Windwalker\Utilities\Arr;

/**
 * The CollectionTraits class.
 *
 * @since  3.2
 */
trait CollectionTrait
{
    /**
     * wrap
     *
     * @param mixed $data
     * @param bool  $includeChildren
     *
     * @return  static
     *
     * @since  3.5.19
     */
    public static function wrap($data, bool $includeChildren = false)
    {
        if (!$data instanceof static) {
            $data = new static($data);
        }

        if ($includeChildren) {
            $data = $data->wrapAll();
        }

        return $data;
    }

    /**
     * wrapAll
     *
     * @param string|null $className
     *
     * @return  static
     *
     * @since  3.5.19
     */
    public function wrapAll(?string $className = null)
    {
        $className = $className ?? static::class;

        return $this->map([$className, 'wrap']);
    }

    /**
     * each
     *
     * @param callable $callback
     *
     * @return  static
     */
    public function each(callable $callback)
    {
        foreach ($this as $key => $value) {
            $return = $callback($value, $key);

            if ($return === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * find
     *
     * @param callable $callback
     * @param bool     $keepKey
     * @param int      $offset
     * @param int      $limit
     *
     * @return static
     */
    public function find(callable $callback, $keepKey = false, $offset = null, $limit = null)
    {
        return $this->bindNewInstance(Arr::find($this->convertArray($this), $callback, $keepKey, $offset, $limit));
    }

    /**
     * query
     *
     * @param array|callable $queries
     * @param bool           $strict
     * @param bool           $keepKey
     *
     * @return  static
     *
     * @since  3.5.8
     */
    public function query($queries = [], bool $strict = false, bool $keepKey = false)
    {
        return $this->bindNewInstance(Arr::query($this->convertArray($this), $queries, $strict, $keepKey));
    }

    /**
     * filter
     *
     * @param callable $callback
     *
     * @return  static
     */
    public function filter(callable $callback = null)
    {
        return $this->find($callback, true);
    }

    /**
     * findFirst
     *
     * @param callable $callback
     *
     * @return  mixed
     */
    public function findFirst(callable $callback = null)
    {
        return Arr::findFirst($this->convertArray($this), $callback);
    }

    /**
     * reject
     *
     * @param callable $callback
     * @param bool     $keepKey
     *
     * @return  static
     */
    public function reject(callable $callback, $keepKey = false)
    {
        return $this->bindNewInstance(Arr::reject($this->convertArray($this), $callback, $keepKey));
    }

    /**
     * partition
     *
     * @param callable $callback
     * @param bool     $keepKey
     *
     * @return  static[]
     */
    public function partition(callable $callback, $keepKey = false)
    {
        $true  = [];
        $false = [];

        if (is_string($callback)) {
            $callback = function ($value) use ($callback) {
                return $callback($value);
            };
        }

        foreach ($this->convertArray($this) as $key => $value) {
            if ($callback($value, $key)) {
                $true[$key] = $value;
            } else {
                $false[$key] = $value;
            }
        }

        if (!$keepKey) {
            $true  = array_values($true);
            $false = array_values($false);
        }

        return [
            $this->bindNewInstance($true),
            $this->bindNewInstance($false),
        ];
    }

    /**
     * apply
     *
     * @param callable $callback
     *
     * @return  static
     */
    public function apply(callable $callback)
    {
        return $this->bindNewInstance($callback($this->convertArray($this)));
    }

    /**
     * pipe
     *
     * @param callable $callback
     *
     * @return  static
     */
    public function pipe(callable $callback)
    {
        return $callback($this);
    }

    /**
     * values
     *
     * @return  static
     */
    public function values()
    {
        return $this->bindNewInstance(array_values($this->convertArray($this)));
    }

    /**
     * first
     *
     * @param callable $conditions
     *
     * @return  mixed
     */
    public function first(callable $conditions = null)
    {
        $array = $this->convertArray($this);

        if ($conditions) {
            foreach ($array as $key => $value) {
                if ($conditions($value, $key)) {
                    return $value;
                }
            }

            return null;
        }

        return $array[array_key_first($array)] ?? null;
    }

    /**
     * last
     *
     * @param callable $conditions
     *
     * @return  mixed
     */
    public function last(callable $conditions = null)
    {
        $array = $this->convertArray($this);

        if ($conditions) {
            $prev = null;

            foreach ($array as $key => $value) {
                if ($conditions($value, $key)) {
                    $prev = $value;
                }
            }

            return $prev;
        }

        return $array[array_key_last($array)] ?? null;
    }

    /**
     * takeout
     *
     * @param string $key
     * @param mixed  $default
     * @param string $delimiter
     *
     * @return  mixed
     */
    public function takeout($key, $default = null, $delimiter = '.')
    {
        return Arr::takeout($this, $key, $default, $delimiter);
    }

    /**
     * chunk
     *
     * @param int  $size
     * @param bool $preserveKeys
     *
     * @return  static
     */
    public function chunk($size, $preserveKeys = null)
    {
        return $this->bindNewInstance(
            array_map(
                [$this, 'bindNewInstance'],
                array_chunk($this->convertArray($this), ...func_get_args())
            )
        );
    }

    /**
     * Mapping all elements.
     *
     * @param callable $callback
     * @param array    ...$args
     * @param bool     $useKeys
     *
     * @return  static  Support chaining.
     *
     * @since   2.0.9
     */
    public function map($callback, ...$args)
    {
        $useKeys = false;

        if (is_bool($args[0] ?? null)) {
            $useKeys = $args[0];
        }

        $keys = $this->keys();

        if ($keys instanceof Collection) {
            $keys = $keys->dump();
        }

        if ($useKeys) {
            $result = array_map($callback, $this->convertArray(clone $this), $keys);
        } else {
            $args = array_map([static::class, 'convertArray'], $args);

            $result = array_map($callback, $this->convertArray(clone $this), ...$args);
        }

        // Keep keys same as origin
        return $this->bindNewInstance(array_combine($keys, $result));
    }

    /**
     * mapRecursive
     *
     * @param callable $callback
     * @param bool     $useKeys
     *
     * @return  static
     *
     * @since  3.5.8
     */
    public function mapRecursive(callable $callback, $useKeys = false)
    {
        return $this->map(static function ($value) use ($callback, $useKeys) {
            if (is_array($value) || is_object($value)) {
                return (new static($value))->map($callback, $useKeys);
            }

            return $callback($value);
        }, $useKeys);
    }

    /**
     * sortColumn
     *
     * @param string $column
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function sortColumn(string $column)
    {
        $array = $this->dump();

        usort($array, static function ($a, $b) use ($column) {
            $aValue = Arr::get($a, $column);
            $bValue = Arr::get($b, $column);

            if (is_stringable($aValue) && is_stringable($bValue)) {
                return strcmp(
                    (string) $aValue,
                    (string) $bValue
                );
            }

            if ($aValue > $bValue) {
                return 1;
            }

            if ($bValue > $aValue) {
                return -1;
            }

            return 0;
        });

        return $this->bindNewInstance($array);
    }

    /**
     * groupBy
     *
     * @param string $column
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function groupBy(string $column)
    {
        return $this->bindNewInstance(Arr::group($this->dump(), $column, Arr::GROUP_TYPE_ARRAY));
    }

    /**
     * keyBy
     *
     * @param string $field
     *
     * @return  static
     */
    public function keyBy(string $field)
    {
        return $this->bindNewInstance(Arr::group($this->dump(), $field, Arr::GROUP_TYPE_KEY_BY));
    }

    /**
     * mapWithKeys
     *
     * @param callable $handler
     *
     * @return  static
     *
     * @since  3.5.12
     */
    public function mapWithKeys(callable $handler)
    {
        return $this->bindNewInstance(Arr::mapWithKeys($this->dump(), $handler));
    }

    /**
     * flatten
     *
     * @param string $delimiter
     * @param int    $depth
     * @param string $prefix
     *
     * @return  static
     *
     * @since  3.5.10
     */
    public function flatten(string $delimiter = '.', int $depth = 0, ?string $prefix = null)
    {
        return $this->bindNewInstance(Arr::flatten($this->dump(), $delimiter, $depth, $prefix));
    }

    /**
     * collapse
     *
     * @param int $depth
     *
     * @return  static
     *
     * @since  3.5.10
     */
    public function collapse(int $depth = 0)
    {
        return $this->flatten('.', $depth)->values();
    }

    /**
     * mapAs
     *
     * @param  string  $class
     *
     * @return  static
     *
     * @since  3.5.13
     */
    public function mapAs(string $class)
    {
        return $this->map(function ($v) use ($class) {
            return new $class($v);
        });
    }

    /**
     * To another class.
     *
     * @param string $class
     *
     * @return  DataInterface
     *
     * @since  3.5.5
     */
    public function as(string $class)
    {
        $obj = new $class();

        if (is_subclass_of($class, DataInterface::class)) {
            if ($this instanceof DataSetInterface) {
                $value = $this->dump();
            } else {
                $value = $this->dump(true);
            }

            /** @var DataInterface $obj */
            $obj->bind($value);
        } else {
            foreach ($this->dump(true) as $k => $v) {
                $obj->$k = $v;
            }
        }

        return $obj;
    }

    /**
     * convertArray
     *
     * @param array|Data|DataSet|static $array
     *
     * @return  array
     */
    protected function convertArray($array)
    {
        if ($array instanceof DataInterface) {
            $array = $array->dump();
        }

        return Arr::toArray($array);
    }

    /**
     * allToArray
     *
     * @param mixed $value
     *
     * @return  array
     */
    public static function allToArray($value)
    {
        if ($value instanceof DataSetInterface) {
            $value = $value->dump(true);
        } elseif ($value instanceof DataInterface) {
            $value = $value->dump(true);
        } elseif ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        } elseif (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = static::allToArray($v);
            }
        }

        return $value;
    }

    /**
     * bindNewInstance
     *
     * @param mixed $data
     *
     * @return  static
     */
    protected function bindNewInstance($data)
    {
        $new = $this->getNewInstance();

        $new->bind($data);

        return $new;
    }

    /**
     * getNewInstance
     *
     * @return  static
     */
    protected function getNewInstance()
    {
        return new static();
    }
}
