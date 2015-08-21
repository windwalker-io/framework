<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Queue;

/**
 * Class Priority
 *
 * @since 2.0
 */
class Priority
{
	const MIN = -300;
	const LOW = -200;
	const BELOW_NORMAL = -100;
	const NORMAL = 0;
	const ABOVE_NORMAL = 100;
	const HIGH = 200;
	const MAX = 300;

	/**
	 * createQueue
	 *
	 * @param array|string $queue
	 * @param integer      $priority
	 *
	 * @return  \SplPriorityQueue
	 *
	 * @deprecated  3.0  Use \Windwalker\Utilities\Queue\PriorityQueue instead.
	 */
	public static function createQueue($queue, $priority = self::NORMAL)
	{
		$queueObject = static::createPriorityQueueObject();

		foreach ((array) $queue as $item)
		{
			$queueObject->insert($item, $priority);
		}

		return $queueObject;
	}

	/**
	 * createPriorityQueueObject
	 *
	 * @return  \SplPriorityQueue
	 *
	 * @deprecated  3.0  Use \Windwalker\Utilities\Queue\PriorityQueue instead.
	 */
	public static function createPriorityQueueObject()
	{
		return new \SplPriorityQueue;
	}
}
