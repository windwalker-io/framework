<?php

declare(strict_types=1);

return [
    'reactor' => [
        'swoole' => [
            //
        ],

        'watch' => [
            'etc/**/*',
            'src/**/*',
            'views/**/*',
        ]
    ]
];
