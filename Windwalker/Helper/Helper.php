<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class Helper
 *
 * @method \Windwalker\Helper\ArrayHelper    Array()
 * @method \Windwalker\Helper\GridHelper     Grid()
 * @method \Windwalker\Helper\HtmlHelper     Html()
 * @method \Windwalker\Helper\JContentHelper JContent()
 * @method \Windwalker\Helper\LanguageHelper Language()
 * @method \Windwalker\Helper\QueryHelper    Query()
 *
 * @since 1.0
 */
class Helper
{
	/**
	 * Property invokers.
	 *
	 * @var array
	 */
	protected static $invokers = array();

	/**
	 * __callStatic
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		$name = ucfirst($name);

		if (empty(self::$invokers[$name]))
		{
			self::$invokers[$name] = new HelperInvoker($name);
		}

		return self::$invokers[$name];
	}
}
