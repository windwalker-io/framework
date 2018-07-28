<?php
/**
 * Part of Windwalker project.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// phpcs:disable

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '/../../../../vendor/autoload.php';

class Application extends \Windwalker\Application\AbstractWebApplication
{
    public function dispatch(Request $request, Response $response, $next = null)
    {
        $response->getBody()->write('Hello World');

        $response = $next($request, $response, $next);

        return $response;
    }
}
$chain = \Windwalker\Middleware\Chain\Psr7ChainBuilder::create(
    [
        function (Request $request, Response $response, $next = null) {
            $input = \Windwalker\IO\PsrInput::create($request);

            show($input->files->get('image.foo.1', '.'));

            show($request->getBody());
            $body = $response->getBody()->__toString();

            $body = ">>>AAA\n" . $body . "\n<<<AAA";

            $response->getBody()->rewind();
            $response->getBody()->write($body);

            return $next($request, $response);
        },
        function (Request $request, Response $response, $next = null) {
            $body = $response->getBody()->__toString();

            $body = ">>>BBB\n" . $body . "\n<<<BBB";

            $response->getBody()->rewind();
            $response->getBody()->write($body);

            return $next($request, $response);
        },
    ]
);

$app = new Application();
$app->setFinalHandler($chain);
$app->execute();
