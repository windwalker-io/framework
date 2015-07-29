<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

/**
 * The CallFilterIterator class.
 *
 * @since  2.0
 */
class CallbackFilterIterator extends \FilterIterator
{
	/**
	 * Property callback.
	 *
	 * @var  callable
	 */
	protected $callback = null;

	/**
	 * Class init.
	 *
	 * @param \Iterator $iterator
	 * @param callable  $callback
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(\Iterator $iterator, $callback)
	{
		if (!is_callable($callback))
		{
			throw new \InvalidArgumentException("Invalid callback");
		}

		$this->callback = $callback;

		parent::__construct($iterator);
	}

	/**
	 * accept
	 *
	 * @return  bool|mixed
	 */
	public function accept()
	{
		$inner = $this->getInnerIterator();

		return call_user_func_array(
			$this->callback,
			array(
				$inner->current(),
				$inner->key(),
				$inner
			)
		);
	}
}
