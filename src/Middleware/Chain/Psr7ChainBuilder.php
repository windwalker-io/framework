<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Middleware\Chain;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Windwalker\Middleware\MiddlewareInterface;
use Windwalker\Middleware\Psr7Middleware;
use Windwalker\Middleware\Psr7InvokableInterface;

/**
 * The Psr7ChainBuilder class.
 *
 * @since  3.0-beta
 */
class Psr7ChainBuilder extends ChainBuilder implements Psr7InvokableInterface
{
	/**
	 * Add a middleware into chain.
	 *
	 * @param mixed $middleware The middleware, can be a object, class name, callback, or middleware object.
	 *                          These type will all convert to middleware object and store in chain.
	 *
	 * @throws  \LogicException
	 * @throws  \InvalidArgumentException
	 *
	 * @return  static Return self to support chaining.
	 */
	public function add($middleware)
	{
		return parent::add($middleware);
	}

	/**
	 * marshalMiddleware
	 *
	 * @param   mixed $middleware
	 *
	 * @return  MiddlewareInterface
	 */
	protected function marshalMiddleware($middleware)
	{
		if (!$middleware instanceof Psr7InvokableInterface)
		{
			$middleware = new Psr7Middleware($middleware);
		}

		return parent::marshalMiddleware($middleware);
	}

	/**
	 * Middleware logic to be invoked.
	 *
	 * @param   Request                      $request  The request.
	 * @param   Response                     $response The response.
	 * @param   callable|MiddlewareInterface $next     The next middleware.
	 *
	 * @return  Response
	 */
	public function __invoke(Request $request, Response $response, $next = null)
	{
		if ($this->getEndMiddleware())
		{
			$end = $this->getEndMiddleware();

			if (count($this->stack))
			{
				$this->stack->bottom()->setNext($end);
			}

			$this->stack->unshift($end);
		}

		if (!count($this->stack))
		{
			return null;
		}

		// Start call chaining.
		$result = $this->stack->top()->execute($request, $response);

		// Remove end middleware so we can re-use this chain.
		if ($this->getEndMiddleware())
		{
			$this->stack->shift();
		}

		return $result;
	}

	/**
	 * Call chaining.
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @return Response
	 */
	public function execute($request = null, $response = null)
	{
		// Start call chaining.
		return call_user_func($this, $request, $response);
	}

	/**
	 * getEndMiddleware
	 *
	 * @return  Psr7InvokableInterface|callable
	 */
	protected function getEndMiddleware()
	{
		if (!$this->endMiddleware)
		{
			$this->endMiddleware = new Psr7Middleware(function ($request, $response)
			{
				return $response;
			});
		}

		return $this->endMiddleware;
	}
}
