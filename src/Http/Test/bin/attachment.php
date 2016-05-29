<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

include_once __DIR__ . '/../../../../vendor/autoload.php';

//show($request = \Windwalker\Http\ServerRequestFactory::fromGlobals());
//
//show($request->getUri());

$server = \Windwalker\Http\WebServer::createServerFromRequest(function ($request, ResponseInterface $response, $finalHandler)
{
	\Windwalker\Http\Helper\StreamHelper::sendAttachment(__DIR__ . '/packet.zip', $response, ['delay' => 10000]);
	die;
}, \Windwalker\Http\Request\ServerRequestFactory::fromGlobals(), new \Windwalker\Http\Response\HtmlResponse);

$server->listen(function ($request, $response) use ($server)
{

});
