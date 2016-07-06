<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

include_once __DIR__ . '/../../../../vendor/autoload.php';

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
	$_SERVER,
	$_GET,
	$_POST,
	$_COOKIE,
	$_FILES
);

$server = \Zend\Diactoros\Server::createServerFromRequest(function (RequestInterface $request, ResponseInterface $response, $done)
{
	$response->getBody()->write("Hello world!");

	show($request->getUri());
}, $request);

$server->listen();
