<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Utilities\Classes;

/**
 * The TraitHelper class.
 *
 * @since  3.0
 */
class TraitHelper
{
	/**
	 * classUsesRecursive
	 *
	 * @link  http://php.net/manual/en/function.class-uses.php#110752
	 *
	 * @param string|object $class
	 * @param bool          $autoload
	 *
	 * @return  array
	 */
	public static function classUsesRecursive($class, $autoload = true)
	{
		$traits = [];

		do
		{
			$traits = array_merge(class_uses($class, $autoload), $traits);
		}
		while($class = get_parent_class($class));

		foreach ($traits as $trait => $same)
		{
			$traits = array_merge(class_uses($trait, $autoload), $traits);
		}

		return array_unique($traits);
	}
}
