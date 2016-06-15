<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Middleware\Chain;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Windwalker\Middleware\MiddlewareInterface;
use Windwalker\Middleware\Psr7Middleware;
use Windwalker\Middleware\Psr7MiddlewareInterface;

/**
 * The Psr7ChainBuilder class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Psr7ChainBuilder extends ChainBuilder implements Psr7MiddlewareInterface
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
		if (!$middleware instanceof Psr7MiddlewareInterface)
		{
			$middleware = new Psr7Middleware($middleware);
		}

		return parent::add($middleware);
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
		if (!count($this->stack))
		{
			return null;
		}

		// Start call chaining.
		return $this->stack->top()->execute($request, $response);
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
	 * @return  Psr7MiddlewareInterface|callable
	 */
	protected function getEndMiddleware()
	{
		return new Psr7Middleware(function ($request, $response)
		{
			return $response;
		});
	}
}
