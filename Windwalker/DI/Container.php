<?php
/**
 * Part of Windwalker RAD framework package.
 *
 * @author     Simon Asika <asika32764@gmail.com>
 * @copyright  Copyright (C) 2014 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\DI;

use Joomla\DI\Container as JoomlaContainer;

/**
 * Class Container
 *
 * @since 2.0
 */
class Container extends JoomlaContainer
{
	/**
	 * Property instance.
	 *
	 * @var Container
	 */
	static protected $instance;

	/**
	 * Property children.
	 *
	 * @var array
	 */
	static protected $children = array();

	/**
	 * getInstance
	 *
	 * @param null $name
	 *
	 * @return Container
	 */
	public static function getInstance($name = null)
	{
		// No name, return root container.
		if (!$name)
		{
			if (self::$instance instanceof static)
			{
				return self::$instance;
			}

			self::$instance = new static;

			return self::$instance;
		}

		// Has name, we return children container.
		if (!empty(self::$children[$name]) && self::$children[$name] instanceof JoomlaContainer)
		{
			return self::$instance;
		}

		self::$children[$name] = new static(static::getInstance());

		return self::$children[$name];
	}
}
