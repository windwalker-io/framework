<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Interface Psr7InvokableInterface
 *
 * @since  3.0
 */
interface Psr7InvokableInterface
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
	public function __invoke(Request $request, Response $response,  $next = null);
}
