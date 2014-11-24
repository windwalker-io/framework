<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\DI\Test\Mock;

/**
 * The Bar class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Bar
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
