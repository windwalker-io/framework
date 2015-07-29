<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Compiler;

use Windwalker\Router\RouteHelper;

/**
 * The TrieCompiler class.
 * 
 * @since  2.0
 */
abstract class TrieCompiler
{
	/**
	 * Property vars.
	 *
	 * @var  array
	 */
	public static $vars = array();

	/**
	 * compile
	 *
	 * @param string $pattern
	 * @param array  $requirements
	 *
	 * @return  string
	 */
	public static function compile($pattern, $requirements = array())
	{
		// Sanitize and explode the pattern.
		$pattern = RouteHelper::sanitize($pattern);

		$vars  = array();
		$regex = array();

		// Loop on each segment
		foreach (explode('/', $pattern) as $segment)
		{
			if ($segment == '')
			{
				// Match root route.
				$regex[] = '';
			}
			elseif ($segment == '*')
			{
				// Match a splat with no variable.
				$regex[] = '.*';
			}
			elseif ($segment[0] == '*')
			{
				// Match a splat and capture the data to a named variable.
				$vars[] = $segment = substr($segment, 1);
				$regex[] = '(?P<' . $segment . '>.*)';
			}
			elseif ($segment[0] == '\\' && $segment[1] == '*')
			{
				// Match an escaped splat segment.
				$regex[] = '\*' . preg_quote(substr($segment, 2));
			}
			elseif ($segment == ':')
			{
				// Match an unnamed variable without capture.
				$regex[] = '[^/]*';
			}
			elseif ($segment[0] == ':')
			{
				// Match a named variable and capture the data.
				$vars[] = $segment = substr($segment, 1);
				$regex[] = static::requirementPattern($segment, $requirements);
			}
			elseif ($segment[0] == '\\' && $segment[1] == ':')
			{
				// Match a segment with an escaped variable character prefix.
				$regex[] = preg_quote(substr($segment, 1));
			}
			else
			{
				// Match the standard segment.
				$regex[] = preg_quote($segment);
			}
		}

		static::$vars = $vars;

		return chr(1) . '^' . implode('/', $regex) . '$' . chr(1);
	}

	/**
	 * requirementPattern
	 *
	 * @param string $name
	 * @param array  $requirements
	 *
	 * @return  string
	 */
	protected static function requirementPattern($name, $requirements = array())
	{
		if (isset($requirements[$name]))
		{
			return "(?P<{$name}>{$requirements[$name]})";
		}

		return "(?P<{$name}>[^/]+)";
	}
}
