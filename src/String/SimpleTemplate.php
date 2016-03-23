<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\String;

use Windwalker\Utilities\ArrayHelper;

/**
 * The SimpleTemplate class.
 *
 * @since  2.1.8
 */
abstract class SimpleTemplate
{
	/**
	 * Parse variable and replace it. This method is a simple template engine.
	 *
	 * Example: The {{ foo.bar.yoo }} will be replace to value of `$data['foo']['bar']['yoo']`
	 *
	 * @param   string $string The template to replace.
	 * @param   array  $data   The data to find.
	 * @param   array  $tags   The variable tags.
	 *
	 * @return  string Replaced template.
	 */
	public static function render($string, $data = array(), $tags = array('{{', '}}'))
	{
		$defaultTags = array('{{', '}}');

		$tags = (array) $tags + $defaultTags;

		list($begin, $end) = $tags;

		$regex = preg_quote($begin) . '\s*(.+?)\s*' . preg_quote($end);

		return preg_replace_callback(
			chr(1) . $regex . chr(1),
			function($match) use ($data)
			{
				$return = ArrayHelper::getByPath($data, $match[1]);

				if (is_array($return) || is_object($return))
				{
					return print_r($return, 1);
				}
				else
				{
					return $return;
				}
			},
			$string
		);
	}
}
