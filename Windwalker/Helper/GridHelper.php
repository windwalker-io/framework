<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class GridHelper
 *
 * @since 1.0
 */
class GridHelper
{
	/**
	 * Show a boolean icon.
	 *
	 * @param   mixed  $value   A variable has value or not.
	 * @param   string $task    Click to call a component task. Not available yet.
	 * @param   array  $options Some options.
	 *
	 * @return  string  A boolean icon HTML string.
	 */
	public static function booleanIcon($value, $task = '', $options = array())
	{
		$class = $value ? 'icon-publish' : 'icon-unpublish';

		return "<i class=\"{$class}\"></i>";
	}
}
