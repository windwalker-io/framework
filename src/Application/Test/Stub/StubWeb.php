<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Application\Test\Stub;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Windwalker\Application\AbstractWebApplication;

/**
 * The AtubApplication class.
 *
 * @since  2.0
 */
class StubWeb extends AbstractWebApplication
{
    /**
     * Property executed.
     *
     * @var string
     */
    public $executed;

    /**
     * Method to close the application.
     *
     * @param   integer|string $message The exit code (optional; default is 0).
     *
     * @return  string
     *
     * @since   2.0
     */
    public function close($message = 0)
    {
        return $message;
    }

    /**
     * Method as the Psr7 WebHttpServer handler.
     *
     * @param  Request  $request  The Psr7 ServerRequest to get request params.
     * @param  Response $response The Psr7 Response interface to [re[are respond data.
     * @param  callable $next     The next handler to support middleware pattern.
     *
     * @return  Response  The returned response object.
     *
     * @since   3.0
     */
    public function dispatch(Request $request, Response $response, $next = null)
    {
        $response->getBody()->write('Hello World');

        return $response;
    }

    /**
     * Method to check to see if headers have already been sent.
     * We wrap headers_sent() function with this method for testing reason.
     *
     * @return  boolean  True if the headers have already been sent.
     *
     * @see     headers_sent()
     *
     * @since   3.0
     */
    public function checkHeadersSent()
    {
        return false;
    }
}
