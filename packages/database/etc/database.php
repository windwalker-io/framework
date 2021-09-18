<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\DatabasePackage;

use Windwalker\ORM\ORM;
use Windwalker\ORM\Subscriber\TruncateValueSubscriber;
use function Windwalker\ref;

return [
    'database' => [
        'enabled' => true,

        'default' => 'local',

        'connections' => [
            'local' => ref('factories.instances.local'),
        ],

        'backup' => [
            'dir' => '@temp/sql-backup',
            'max' => 20
        ],

        'providers' => [
            DatabasePackage::class,
        ],

        'listeners' => [
            ORM::class => [
                TruncateValueSubscriber::class
            ]
        ],

        'bindings' => [
            //
        ],

        'factories' => [
            'instances' => [
                'local' => fn (DatabaseFactory $factory) => $factory->create(
                    env('DATABASE_DRIVER'),
                    [
                        'host' => env('DATABASE_HOST') ?: 'localhost',
                        'dbname' => env('DATABASE_NAME'),
                        'user' => env('DATABASE_USER'),
                        'password' => env('DATABASE_PASSWORD'),
                        'port' => env('DATABASE_PORT'),
                        'prefix' => env('DATABASE_TABLE_PREFIX'),
                    ]
                ),
            ],
        ],
    ]
];
