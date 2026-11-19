<?php

declare(strict_types=1);

use Windwalker\Core\Provider\RouterProvider;
use Windwalker\WebSocket\Middleware\WebSocketRoutingMiddleware;

return [
    'routing' => [
        'routes' => [
            __DIR__ . '/../../routes/websocket.route.php',
        ],
        'providers' => [
            RouterProvider::class,
        ],
    ],

    'middlewares' => [
        WebSocketRoutingMiddleware::class,
    ],
];
