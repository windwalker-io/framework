<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

$autoload = __DIR__ . '/../../vendor/autoload.php';

if (!is_file($autoload)) {
    $autoload = __DIR__ . '/../../../../vendor/autoload.php';
}

include $autoload;

use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Server\HttpServer;
use Windwalker\Http\Server\ReactServer;

$server = new HttpServer(new ReactServer('0.0.0.0', 8888));
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
    static function ($event) {
        echo $event->getException();
    }
);
$server->listen();
