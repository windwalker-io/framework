<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Event;

/**
 * The DispatcherMapper class.
 *
 * @since  2.1
 */
class ListenerMapper
{
	/**
	 * Property maps.
	 *
	 * @var  array
	 */
	protected static $maps = [];

	/**
	 * mapListener
	 *
	 * @param   string           $targetClass
	 * @param   object|callable  $listener
	 *
	 * @return  boolean
	 */
	public static function register($targetClass, $listener)
	{
		if (!is_subclass_of($targetClass, 'Windwalker\Event\DispatcherAwareInterface'))
		{
			throw new \InvalidArgumentException('Target class should be a DispatcherInterface.');
		}

		$targetClass = strtolower(trim($targetClass, '\\'));

		if (is_string($listener) && class_exists($listener))
		{
			$listener = new $listener;
		}

		if (!is_object($listener) || is_callable($listener))
		{
			throw new \InvalidArgumentException('Listener is not an object or callable.');
		}

		if (!isset(static::$maps[$targetClass]))
		{
			static::$maps[$targetClass] = [];
		}

		static::$maps[$targetClass][] = $listener;

		return true;
	}

	/**
	 * add
	 *
	 * @param   DispatcherAwareInterface  $target
	 *
	 * @return  boolean
	 */
	public static function add(DispatcherAwareInterface $target)
	{
		if (!$target instanceof DispatcherAwareInterface)
		{
			return false;
		}

		$targetClass = strtolower(get_class($target));

		if (empty(static::$maps[$targetClass]))
		{
			return false;
		}

		$listeners = static::$maps[$targetClass];

		$dispatcher = $target->getDispatcher();

		foreach ($listeners as $listener)
		{
			$dispatcher->addListener($listener);
		}

		return true;
	}
}
