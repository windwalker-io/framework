<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Router;

/**
 * The RouteHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class RouteHelper
{
	/**
	 * Sanitize and explode the pattern.
	 *
	 * @param string $pattern
	 *
	 * @return  string
	 */
	public static function sanitize($pattern)
	{
		return trim(parse_url((string) $pattern, PHP_URL_PATH), ' /');
	}

	/**
	 * convertVariables
	 *
	 * @param array $matches
	 * @param array &$vars
	 *
	 * @return  array
	 */
	public static function getVariables($matches, &$vars = null)
	{
		if (!$matches)
		{
			return array();
		}

		if ($vars === null)
		{
			$vars = array();
		}

		foreach ($matches as $i => $var)
		{
			if (is_numeric($i))
			{
				continue;
			}

			if (strpos($var, '/') !== false)
			{
				$var = explode('/', $var);
			}

			$vars[$i] = $var;
		}

		return $vars;
	}
}
