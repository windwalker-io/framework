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
}, \Windwalker\Http\Request\ServerRequestFactory::createFromGlobals(), new \Windwalker\Http\Response\HtmlResponse);

$server->listen(function ($request, $response) use ($server)
{

});
