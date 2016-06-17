<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
	const SORT_ASC = 'ASC';
	const SORT_DESC = 'DESC';

	/**
	 * The middleware chain.
	 *
	 * @var  MiddlewareInterface[]|\SplStack
	 */
	protected $stack;

	/**
	 * ChainBuilder constructor.
	 *
	 * @param MiddlewareInterface[] $middlewares
	 * @param string                $sort
	 */
	public function __construct(array $middlewares = array(), $sort = self::SORT_DESC)
	{
		$this->stack = $this->createStack();

		$this->addMiddlewares($middlewares, $sort);
	}

	/**
	 * Add a middleware into chain.
	 *
	 * @param mixed $middleware The middleware, can be a object, class name, callback, or middleware object.
	 *                       These type will all convert to middleware object and store in chain.
	 *
	 * @throws  \LogicException
	 * @throws  \InvalidArgumentException
	 *
	 * @return  static Return self to support chaining.
	 */
	public function add($middleware)
	{
		if (is_string($middleware) && class_exists($middleware))
		{
			$reflection = new \ReflectionClass($middleware);

			if (!$reflection->isInstantiable())
			{
				throw new \LogicException(sprintf('Element %s should be an instantiable class name.'));
			}

			$args = func_get_args();

			array_shift($args);

			$object = $reflection->newInstanceArgs($args);
		}
		elseif ($middleware instanceof MiddlewareInterface)
		{
			$object = $middleware;
		}
		elseif (is_callable($middleware))
		{
			$object = new CallbackMiddleware($middleware);
		}
		else
		{
			throw new \InvalidArgumentException('Not valid MiddleChaining element.');
		}

		$object->setNext($this->stack->top());

		$this->stack[] = $object;

		return $this;
	}

	/**
	 * Call chaining.
	 *
	 * @param  mixed $data
	 *
	 * @return mixed
	 */
	public function execute($data = null)
	{
		if (!count($this->stack))
		{
			return null;
		}

		// Start call chaining.
		return $this->stack->top()->execute($data);
	}

	/**
	 * createStack
	 *
	 * @return  \SplStack
	 */
	protected function createStack()
	{
		$stack = new \SplStack;
		$stack->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_KEEP);
		$stack[] = $this->getEndMiddleware();

		return $stack;
	}

	/**
	 * Method to get property Stack
	 *
	 * @return  \SplStack|MiddlewareInterface[]
	 */
	public function getStack()
	{
		return $this->stack;
	}

	/**
	 * Method to set property stack
	 *
	 * @param   \SplStack $stack
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setStack(\SplStack $stack)
	{
		$this->stack = $stack;

		return $this;
	}

	/**
	 * reset
	 *
	 * @return  void
	 */
	protected function reset()
	{
		$this->stack = $this->createStack();
	}

	/**
	 * addMiddlewares
	 *
	 * @param array  $middlewares
	 * @param string $sort
	 *
	 * @return  static
	 */
	public function addMiddlewares(array $middlewares, $sort = self::SORT_DESC)
	{
		if ($sort == static::SORT_DESC)
		{
			$middlewares = array_reverse($middlewares);
		}

		foreach ($middlewares as $middleware)
		{
			$this->add($middleware);
		}

		return $this;
	}

	/**
	 * getEndMiddleware
	 *
	 * @return  MiddlewareInterface
	 */
	protected function getEndMiddleware()
	{
		return new EndMiddleware;
	}

	/**
	 * dumpStack
	 *
	 * @return  array
	 */
	public function dumpStack()
	{
		return iterator_to_array(clone $this->stack);
	}
}
