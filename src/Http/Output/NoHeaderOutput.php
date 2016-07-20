<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Output;

/**
 * The NoHeaderOutput class.
 *
 * @since  3.0
 */
class NoHeaderOutput extends Output
{
	/**
	 * Method to override parent header() and do nothing.
	 *
	 * @param   string   $string   The header string.
	 * @param   boolean  $replace  The optional replace parameter indicates whether the header should
	 *                             replace a previous similar header, or add a second header of the same type.
	 * @param   integer  $code     Forces the HTTP response code to the specified value. Note that
	 *                             this parameter only has an effect if the string is not empty.
	 *
	 * @return  static
	 */
	public function header($string, $replace = true, $code = null)
	{
		return $this;
	}
}
