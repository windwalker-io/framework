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
 * The Psr7ChainBuilder class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Psr7ChainBuilder implements Psr7MiddlewareInterface
{
	/**
	 * Property queue.
	 *
	 * @var  array
	 */
	protected $queue = array();

	/**
	 * create
	 *
	 * @param array $queue
	 *
	 * @return  static
	 */
	public static function create(array $queue = array())
	{
		return new static($queue);
	}

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
	 * add
	 *
	 * @param callable $middleware
	 *
	 * @return  static
	 */
	public function push($middleware)
	{
		$this->queue[] = $middleware;

		return $this;
	}

	/**
	 * unshift
	 *
	 * @param callable $middleware
	 *
	 * @return  static
	 */
	public function unshift($middleware)
	{
		array_unshift($this->queue, $middleware);

		return $this;
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
	public function __invoke(Request $request, Response $response,  $next = null)
	{
		$runner = new Psr7Runner($this->queue);

		return $runner($request, $response);
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
