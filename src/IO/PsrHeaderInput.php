<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\IO;

use Windwalker\Http\Helper\HeaderHelper;

/**
 * The HeaderInput class.
 *
 * @since  {DEPLOY_VERSION}
 */
class PsrHeaderInput extends Input
{
	/**
	 * prepareSource
	 *
	 * @param array $source
	 * @param bool  $reference
	 *
	 * @return  void
	 */
	public function prepareSource(&$source = null, $reference = false)
	{
		$headers = array();

		foreach ((array) $source as $key => $value)
		{
			$headers[HeaderHelper::normalizeHeaderName($key)] = implode(', ', $value);
		}

		parent::prepareSource($headers, $reference);
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
	public function get($name, $default = null, $filter = 'cmd', $separator = '.')
	{
		$name = HeaderHelper::normalizeHeaderName($name);

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
		$name = HeaderHelper::normalizeHeaderName($name);

		parent::set($name, $value, $separator);
	}
}
