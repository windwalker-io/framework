<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\OptionSet;

/**
 * Class OptionSet
 *
 * @since 1.0
 */
class OptionSet extends \ArrayObject
{
	/**
	 * Property instance.
	 *
	 * @var OptionSet
	 */
	protected static $instance;

	/**
	 * getInstance
	 *
	 * @return  OptionSet
	 */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new static;
		}

		return self::$instance;
	}
}
