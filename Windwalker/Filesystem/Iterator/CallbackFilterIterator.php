<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class CallbackFilterIterator
 *
 * @since 1.0
 */
class CallbackFilterIterator extends \FilterIterator
{
	/**
	 * Property callback.
	 *
	 * @var  callable
	 */
	protected $callback;

	/**
	 * Constructor.
	 *
	 * @param Traversable $iter
	 * @param \Closure    $callback
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(Traversable $iter, $callback)
	{
		parent::__construct($iter);

		if (!is_callable($callback))
		{
			throw new InvalidArgumentException('Invalid Callback');
		}

		$this->callback = $callback;
	}

	/**
	 * accept
	 *
	 * @return  mixed
	 */
	public function accept()
	{
		return call_user_func(
			$this->callback,
			$this->current(),
			$this->key(),
			$this->getInnerIterator()
		);
	}
}
