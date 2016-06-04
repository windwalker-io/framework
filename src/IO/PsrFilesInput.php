<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO;

use Psr\Http\Message\UploadedFileInterface;
use Windwalker\Filter\InputFilter;

/**
 * Windwalker Input Files Class
 *
 * @since  2.0
 */
class PsrFilesInput extends Input
{
	/**
	 * Prepare source.
	 *
	 * @param   array    $source     Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   boolean  $reference  If set to true, he source in first argument will be reference.
	 *
	 * @return  void
	 */
	public function prepareSource(&$source = null, $reference = false)
	{
		$this->data = $source;
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
	 * @param   mixed   $default  The default value to return if the named property does not exist.
	 * @param   string  $filter   The filter to apply to the value.
	 *
	 * @return  UploadedFileInterface|UploadedFileInterface[]  The filtered input value.
	 *
	 * @see     JFilterInput::clean
	 * @since   2.0
	 */
	public function get($name, $default = null, $filter = InputFilter::RAW)
	{
		return parent::get($name, $default, $filter);
	}

	/**
	 * getByPath
	 *
	 * @param   string  $paths
	 * @param   mixed   $default
	 * @param   string  $filter
	 * @param   string  $separator
	 *
	 * @return  UploadedFileInterface|UploadedFileInterface[]
	 */
	public function getByPath($paths, $default = null, $filter = InputFilter::CMD, $separator = '.')
	{
		return parent::getByPath($paths, $default, $filter, $separator);
	}

	/**
	 * setByPath
	 *
	 * @param string  $paths
	 * @param mixed   $value
	 * @param string  $separator
	 *
	 * @return bool
	 */
	public function setByPath($paths, $value, $separator = '.')
	{
		return true;
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 *                              If empty and datasource is null, all the input data will be returned
	 *                              but filtered using the default case in JFilterInput::clean.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   2.0
	 */
	public function getArray(array $vars = array(), $datasource = null)
	{
		if (is_array($vars) && $datasource === null)
		{
			return $this->data;
		}
		
		return parent::getArray($vars, $datasource);
	}

	/**
	 * Sets a value.
	 *
	 * @param   string  $name   The name of the input property to set.
	 * @param   mixed   $value  The value to assign to the input property.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function set($name, $value)
	{
	}
}
