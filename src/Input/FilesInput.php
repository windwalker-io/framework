<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Input;

use Joomla\Filter\InputFilter;

/**
 * Joomla! Input Files Class
 *
 * @since  1.0
 */
class FilesInput extends Input
{
	/**
	 * The pivoted data from a $_FILES or compatible array.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $decodedData = array();

	/**
	 * Constructor.
	 *
	 * @param   array       $source Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   InputFilter $filter The input filter object.
	 *
	 * @since   1.0
	 */
	public function __construct($source = null, InputFilter $filter = null)
	{
		$this->filter = $filter ? : new InputFilter;

		$this->data = &$_FILES;
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
	 * @param   mixed   $default  The default value to return if the named property does not exist.
	 * @param   string  $filter   The filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @see     JFilterInput::clean
	 * @since   1.0
	 */
	public function get($name, $default = null, $filter = 'cmd')
	{
		if (isset($this->data[$name]))
		{
			$results = $this->decodeData(
				array(
					$this->data[$name]['name'],
					$this->data[$name]['type'],
					$this->data[$name]['tmp_name'],
					$this->data[$name]['error'],
					$this->data[$name]['size']
				)
			);

			return $results;
		}

		return $default;
	}

	/**
	 * Method to decode a data array.
	 *
	 * @param   array  $data  The data array to decode.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	protected function decodeData(array $data)
	{
		$result = array();

		if (is_array($data[0]))
		{
			foreach ($data[0] as $k => $v)
			{
				$result[$k] = $this->decodeData(array($data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]));
			}

			return $result;
		}

		return array('name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]);
	}

	/**
	 * Sets a value.
	 *
	 * @param   string  $name   The name of the input property to set.
	 * @param   mixed   $value  The value to assign to the input property.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function set($name, $value)
	{
	}
}
