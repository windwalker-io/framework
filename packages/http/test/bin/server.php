<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Server\HttpServer;
use Windwalker\Http\Server\PhpServer;

error_reporting(-1);

$autoload = __DIR__ . '/../../vendor/autoload.php';

if (!is_file($autoload)) {
    $autoload = __DIR__ . '/../../../../vendor/autoload.php';
}

include $autoload;

$server = new HttpServer();
$server->setHandler(fn(PhpServer $server) => $server->handle(ServerRequestFactory::createFromGlobals()));
$server->on(
    'request',
    static function (RequestEvent $event) {
        $app = require __DIR__ . '/app.php';

        $res = $app($event->getRequest());

        $event->setResponse($res);
    }
);
$server->on(
    'error',
    static function (ErrorEvent $event) {
        echo $event->getException();
    }
);
$server->listen();
