<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Grid;

/**
 * The KeyValueGrid class.
 * 
 * @since  2.1.1
 */
class KeyValueGrid extends Grid
{
	const COL_KEY   = 'key';
	const COL_VALUE = 'value';
	const ROW       = 'row';

	/**
	 * create
	 *
	 * @param array $options
	 *
	 * @return static
	 */
	public static function create($options = array())
	{
		return new static($options);
	}

	/**
	 * Class init.
	 *
	 * @param array  $options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->setColumns(array(static::COL_KEY, static::COL_VALUE));
	}

	/**
	 * addHeader
	 *
	 * @param string $keyTitle
	 * @param string $valueTitle
	 * @param array  $options
	 *
	 * @return  static
	 */
	public function addHeader($keyTitle = 'Key', $valueTitle = 'Value', $options = array())
	{
		$this->addRow((array) $this->getValue($options, static::ROW), static::ROW_HEAD)
			->setRowCell(static::COL_KEY, $keyTitle, (array) $this->getValue($options, static::COL_KEY))
			->setRowCell(static::COL_VALUE, $valueTitle, (array) $this->getValue($options, static::COL_VALUE));

		return $this;
	}

	/**
	 * addItem
	 *
	 * @param string $key
	 * @param string $value
	 * @param array  $options
	 *
	 * @return static
	 */
	public function addItem($key, $value = null, $options = array())
	{
		if (is_array($value))
		{
			$value = print_r($value, 1);
		}

		$this->addRow((array) $this->getValue($options, static::ROW))
			->setRowCell(static::COL_KEY, $key, (array) $this->getValue($options, static::COL_KEY));

		if ($value !== false)
		{
			$this->setRowCell(static::COL_VALUE, $value, (array) $this->getValue($options, static::COL_VALUE));
		}

		return $this;
	}

	/**
	 * addItems
	 *
	 * @param string  $items
	 * @param array   $options
	 *
	 * @return  static
	 */
	public function addItems($items = null, $options = array())
	{
		$this->configure($items, function(KeyValueGrid $grid, $key, $value) use ($options)
		{
			$grid->addItem($key, $value, $options);
		});

		return $this;
	}

	/**
	 * addTitle
	 *
	 * @param string $name
	 * @param array  $options
	 *
	 * @return  static
	 */
	public function addTitle($name, $options = array())
	{
		$options[static::COL_KEY]['colspan'] = 2;

		$this->addItem($name, false, $options);

		return $this;
	}

	/**
	 * configureRows
	 *
	 * @param   array     $items
	 * @param   callable  $handler
	 *
	 * @return  static
	 */
	public function configure($items, $handler)
	{
		if (!is_callable($handler))
		{
			throw new \InvalidArgumentException(__METHOD__ . ' Handler should be callable.');
		}

		if (!$items instanceof \Traversable && !is_array($items))
		{
			throw new \InvalidArgumentException(__METHOD__ . ' items should be array or iterator.');
		}

		foreach ($items as $key => $item)
		{
			call_user_func($handler, $this, $key, $item);
		}

		return $this;
	}

	/**
	 * getValue
	 *
	 * @param array  $options
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	protected function getValue(array $options, $name, $default = null)
	{
		if (isset($options[$name]))
		{
			return $options[$name];
		}

		return $default;
	}
}
