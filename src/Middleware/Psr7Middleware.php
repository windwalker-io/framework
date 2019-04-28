<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * The Psr7Middleware class.
 *
 * @since  3.0
 */
class Psr7Middleware extends CallbackMiddleware implements Psr7InvokableInterface
{
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
        return call_user_func($this->handler, $request, $response, $this->next);
    }

    /**
     * Call next middleware.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function execute($request = null, $response = null)
    {
        return call_user_func($this, $request, $response);
    }
}
