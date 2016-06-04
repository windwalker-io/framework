<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Middleware\Chain;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Windwalker\Middleware\MiddlewareInterface;
use Windwalker\Middleware\Psr7MiddlewareInterface;

/**
 * Psr7 Middleware Runner, will only runs once and drop.
 *
 * @since  {DEPLOY_VERSION}
 */
class Psr7Runner implements Psr7MiddlewareInterface
{
	/**
	 * Property queue.
	 *
	 * @var  array
	 */
	protected $queue = array();

	/**
	 * Psr7ChainBuilder constructor.
	 *
	 * @param array $queue
	 */
	public function __construct(array $queue = array())
	{
		$this->setQueue($queue);
	}

	/**
	 * Middleware logic to be invoked.
	 *
	 * This method will be the next handler in every middleware, it calls next middleware by
	 * invoke self recursively.
	 *
	 * @param   Request                      $request  The request.
	 * @param   Response                     $response The response.
	 * @param   callable|MiddlewareInterface $next     The next middleware.
	 *
	 * @return  Response
	 */
	public function __invoke(Request $request, Response $response, callable $next = null)
	{
		$handler = array_shift($this->queue);

		// If no more handler, add an End Middleware
		if (!$handler)
		{
			$handler = function (Request $request, Response $response, callable $next = null)
			{
			    return $response;
			};
		}

		if (!is_callable($handler))
		{
			if (is_object($handler))
			{
				$handler = get_class($handler);
			}
			elseif (is_array($handler))
			{
				$handler = implode('::', $handler);
			}

			throw new \InvalidArgumentException(sprintf('"%s" is not callable.', $handler));
		}

		return $handler($request, $response, $this);
	}

	/**
	 * Method to get property Queue
	 *
	 * @return  array
	 */
	public function getQueue()
	{
		return $this->queue;
	}

	/**
	 * Method to set property queue
	 *
	 * @param   array $queue
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setQueue(array $queue)
	{
		$this->queue = $queue;

		return $this;
	}
}
