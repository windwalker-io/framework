<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Utilities\Queue;

/**
 * Class Priority
 *
 * @since 1.0
 */
class Priority
{
	const MIN = -3;
	const LOW = -2;
	const BELOW_NORMAL = -1;
	const NORMAL = 0;
	const ABOVE_NORMAL = 1;
	const HIGH = 2;
	const MAX = 3;

	/**
	 * createQueue
	 *
	 * @param array|string $queue
	 * @param integer      $priority
	 *
	 * @return  \SplPriorityQueue
	 */
	public static function createQueue($queue, $priority = Priority::NORMAL)
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
	 */
	public static function createPriorityQueueObject()
	{
		return new \SplPriorityQueue;
	}
}
 