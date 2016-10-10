<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Utilities\Classes;

/**
 * The OptionAccessTrait class.
 *
 * @since  3.0.1
 */
trait OptionAccessTrait
{
	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * Method to get property Options
	 *
	 * @param   string $name
	 * @param   mixed  $default
	 *
	 * @return  mixed
	 */
	public function getOption($name, $default = null)
	{
		if (array_key_exists($name, $this->options))
		{
			return $this->options[$name];
		}

		return $default;
	}

	/**
	 * Method to set property options
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * Method to get property Options
	 *
	 * @return  int
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Method to set property options
	 *
	 * @param   int $options
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}
}
