<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO;

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
	 * @param   string $name      Name of the value to get.
	 * @param   mixed  $default   Default value to return if variable does not exist.
	 * @param   string $filter    Filter to apply to the value.
	 * @param   string $separator Separator for path.
	 *
	 * @return mixed The filtered input value.
	 *
	 * @since   2.0
	 */
	public function get($name, $default = null, $filter = 'raw', $separator = '.')
	{
		return parent::get($name, $default, $filter, $separator);
	}

	/**
	 * Sets a value
	 *
	 * @param   string $name      Name of the value to set.
	 * @param   mixed  $value     Value to assign to the input.
	 * @param   string $separator Symbol to separate path.
	 *
	 * @since   2.0
	 */
	public function set($name, $value, $separator = '.')
	{
	}
}
