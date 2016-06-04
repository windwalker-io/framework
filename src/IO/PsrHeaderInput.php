<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\IO;

use Windwalker\Filter\InputFilter;
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
	 * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
	 * @param   mixed   $default  The default value to return if the named property does not exist.
	 * @param   string  $filter   The filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @see     JFilterInput::clean
	 * @since   2.0
	 */
	public function get($name, $default = null, $filter = InputFilter::STRING)
	{
		$name = HeaderHelper::normalizeHeaderName($name);

		return parent::get($name, $default, $filter);
	}

	/**
	 * Sets a value
	 *
	 * @param   string $name  Name of the value to set.
	 * @param   mixed  $value Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function set($name, $value)
	{
		$name = HeaderHelper::normalizeHeaderName($name);

		parent::set($name, $value);
	}
}
