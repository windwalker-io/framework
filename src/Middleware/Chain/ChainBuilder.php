<?php
/**
 * Part of windwalker-middleware project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware\Chain;

use Windwalker\Middleware\CallbackMiddleware;
use Windwalker\Middleware\EndMiddleware;
use Windwalker\Middleware\MiddlewareInterface;

/**
 * The Chain Builder
 *
 * @since 2.0
 */
class ChainBuilder
{
	/**
	 * The middleware chain.
	 *
	 * @var  MiddlewareInterface[]
	 */
	protected $stack = array();

	/**
	 * Add a middleware into chain.
	 *
	 * @param mixed $element The middleware, can be a object, class name, callback, or middleware object.
	 *                       These type will all convert to middleware object and store in chain.
	 *
	 * @throws  \LogicException
	 * @throws  \InvalidArgumentException
	 *
	 * @return  ChainBuilder Return self to support chaining.
	 */
	public function add($element)
	{
		if (is_string($element))
		{
			$reflection = new \ReflectionClass($element);

			if (!$reflection->isInstantiable())
			{
				throw new \LogicException(sprintf('Element %s should be an instantiable class name.'));
			}

			$args = func_get_args();

			array_shift($args);

			$object = $reflection->newInstanceArgs($args);
		}
		elseif (is_callable($element))
		{
			$object = new CallbackMiddleware($element);
		}
		elseif (is_subclass_of($element, 'Windwalker\\Middleware\\MiddlewareInterface'))
		{
			$object = $element;
		}
		else
		{
			throw new \InvalidArgumentException('Not valid MiddleChaining element.');
		}

		$this->stack[] = $object;

		return $this;
	}

	/**
	 * Call chaining.
	 *
	 * @return  mixed
	 */
	public function call()
	{
		if (!count($this->stack))
		{
			return null;
		}

		// Set end middleware
		$last = end($this->stack);

		if (!($last instanceof EndMiddleware))
		{
			$this->stack[] = new EndMiddleware;
		}

		reset($this->stack);

		// Set chaining
		/** @var MiddlewareInterface $previous */
		$previous = null;

		foreach ($this->stack as $ware)
		{
			if ($previous)
			{
				$previous->setNext($ware);
			}

			$previous = $ware;
		}

		// Start call chaining.
		return $this->stack[0]->call();
	}
}
