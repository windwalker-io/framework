<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Data\Traits;

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
	 * each
	 *
	 * @param callable $callback
	 *
	 * @return  static
	 */
	public function each(callable $callback)
	{
		foreach ($this as $key => $value)
		{
			$return = call_user_func($callback, $value, $key);

			if ($return === false)
			{
				break;
			}
		}

		return $this;
	}

	/**
	 * filter
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
	 * filter
	 *
	 * @param callable $callback
	 *
	 * @return  static
	 */
	public function filter(callable $callback = null)
	{
		return $this->find($callback);
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
		$true = [];
		$false = [];

		if (is_string($callback))
		{
			$callback = function ($value) use ($callback)
			{
			    return $callback($value);
			};
		}

		foreach ($this->convertArray($this) as $key => $value)
		{
			if ($callback($value, $key))
			{
				$true[$key] = $value;
			}
			else
			{
				$false[$key] = $value;
			}
		}

		if (!$keepKey)
		{
			$true = array_values($true);
			$false = array_values($false);
		}

		return [
			$this->bindNewInstance($true),
			$this->bindNewInstance($false)
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
		return $this->bindNewInstance(call_user_func($callback, $this->convertArray($this)));
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
		return call_user_func($callback, $this);
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

		if ($conditions)
		{
			foreach ($array as $key => $value)
			{
				if ($conditions($value, $key))
				{
					return $value;
				}
			}

			return null;
		}

		return array_shift($array);
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

		if ($conditions)
		{
			$prev = null;

			foreach ($array as $key => $value)
			{
				if ($conditions($value, $key))
				{
					$prev = $value;
				}
			}

			return $prev;
		}

		return array_pop($array);
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
			array_map([$this, 'bindNewInstance'], array_chunk($this->convertArray($this), $size, $preserveKeys))
		);
	}

	/**
	 * Mapping all elements.
	 *
	 * @param   callable  $callback
	 *
	 * @return  static  Support chaining.
	 *
	 * @since   2.0.9
	 */
	public function map($callback)
	{
		$keys = $this->keys();

		// Keep keys same as origin
		return $this->bindNewInstance(array_combine($keys, array_map($callback, $this->convertArray(clone $this), $keys)));
	}

	/**
	 * convertArray
	 *
	 * @param array|Data|DataSet $array
	 *
	 * @return  array
	 */
	protected function convertArray($array)
	{
		if ($array instanceof static)
		{
			$array = $array->dump();
		}

		return $array;
	}

	/**
	 * allToArray
	 *
	 * @param   mixed  $value
	 *
	 * @return  array
	 */
	public static function allToArray($value)
	{
		if ($value instanceof DataSetInterface)
		{
			$value = $value->dump(true);
		}
		elseif ($value instanceof DataInterface)
		{
			$value = $value->dump(true);
		}
		elseif ($value instanceof \Traversable)
		{
			$value = iterator_to_array($value);
		}
		elseif (is_object($value))
		{
			$value = get_object_vars($value);
		}

		if (is_array($value))
		{
			foreach ($value as &$v)
			{
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
		return new static;
	}
}
