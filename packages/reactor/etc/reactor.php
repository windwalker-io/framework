<?php

declare(strict_types=1);

return [
    'reactor' => [
        'swoole' => [
            //
        ],

        'websocket' => [
            'user_mapping' => [
                'size' => 1024,
                'length' => 32768
            ],

            'request_registry' => [
                'size' => 100000,
                'length' => 2048
            ],
        ],

        'watch' => [
            'etc/**/*',
            'src/**/*',
            'views/**/*',
        ]
    ]
];
