<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DI\Test\Mock;

/**
 * The Bar class.
 * 
 * @since  2.0
 */
class Bar2
{
	/**
	 * Property queue.
	 *
	 * @var  \SplPriorityQueue
	 */
	public $queue = null;

	/**
	 * Property stack.
	 *
	 * @var  \SplStack
	 */
	public $stack = null;

	/**
	 * Class init.
	 *
	 * @param \SplPriorityQueue $queue
	 * @param \SplStack         $stack
	 */
	public function __construct(\SplPriorityQueue $queue, \SplStack $stack)
	{
		$this->queue = $queue;
		$this->stack = $stack;
	}
}
