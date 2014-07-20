<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View;

/**
 * Class AbstractView
 *
 * @since 1.0
 */
abstract class AbstractView implements ViewInterface
{
	/**
	 * Property data.
	 *
	 * @var  array
	 */
	protected $data = array();

	/**
	 * View class init.
	 *
	 * @param array $data
	 */
	public function __construct($data = array())
	{
		$this->data = (array) $data;
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string $output The output to escape.
	 *
	 * @return  string  The escaped output.
	 */
	public function escape($output)
	{
		return $output;
	}

	/**
	 * get
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return  null
	 */
	public function get($key, $default = null)
	{
		return isset($this->data[$key]) ? $this->data[$key] : $default;
	}

	/**
	 * set
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;

		return $this;
	}

	/**
	 * getData
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
	 * @param   array $data
	 *
	 * @return  AbstractView  Return self to support chaining.
	 */
	public function setData($data)
	{
		$this->data = (array) $data;

		return $this;
	}
}
 