<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '/../../../../vendor/autoload.php';

class Application extends \Windwalker\Application\AbstractWebApplication
{
	public function dispatch(Request $request, Response $response, callable $next = null)
	{
		return $response->getBody()->write('Hello World');
	}
}

$app = new Application;
$app->execute();
