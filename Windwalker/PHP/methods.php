<?php
/**
 * Part of Windwalker RAD framework package.
 *
 * @author     Simon Asika <asika32764@gmail.com>
 * @copyright  Copyright (C) 2014 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!function_exists('with'))
{
	/**
	 * Return the given object. Useful for chaining. This function is from Joomla!Framework:
	 * https://github.com/joomla/joomla-framework/blob/staging/src/Joomla/PHP/methods.php
	 *
	 * This method provides forward compatibility for the PHP 5.4 feature Class member access on instantiation.
	 * e.g. (new Foo)->bar().
	 * See: http://php.net/manual/en/migration54.new-features.php
	 *
	 * @param   mixed  $object  The object to return.
	 *
	 * @since  1.0
	 *
	 * @return mixed
	 */
	function with($object)
	{
		return $object;
	}
}
