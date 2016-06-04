<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Interface Psr7MiddleInterface
 *
 * @since  {DEPLOY_VERSION}
 */
interface Psr7MiddlewareInterface
{
	/**
	 * Middleware logic to be invoked.
	 *
	 * @param   Request                      $request   The request.
	 * @param   Response                     $response  The response.
	 * @param   callable|MiddlewareInterface $next      The next middleware.
	 *                                                
	 * @return  Response
	 */
	public function __invoke(Request $request, Response $response, callable $next = null);
}
