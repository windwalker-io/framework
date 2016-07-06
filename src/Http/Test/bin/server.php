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

//show($request = \Windwalker\Http\ServerRequestFactory::fromGlobals());
//
//show($request->getUri());

$server = \Windwalker\Http\WebHttpServer::create(function ($request, ResponseInterface $response, $finalHandler)
{
	// $response = $response->withHeader('Content-Type', 'application/json');

//	$response->getBody()->write('Hello World!');

	$response = new \Windwalker\Http\Response\HtmlResponse('<root><f>中文 World!</f></root>');

	$response = $response->withHeader('asd', 123);

	$response = $finalHandler($request, $response);

	return $response;
}, \Windwalker\Http\Request\ServerRequestFactory::createFromGlobals(), new \Windwalker\Http\Response\HtmlResponse);

$server->listen(function ($request, ResponseInterface $response) use ($server)
{
	$server->cachable($server::CACHE_CUSTOM_HEADER);

	return $response = $server->getCompressor()->compress($response);
});
