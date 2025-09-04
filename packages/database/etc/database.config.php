<?php

declare(strict_types=1);

namespace App\Config;

use Windwalker\Core\Attributes\ConfigModule;
use Windwalker\Core\Factory\DatabaseServiceFactory;
use Windwalker\Database\DatabasePackage;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Subscriber\TruncateValueSubscriber;
use Windwalker\Pool\PoolInterface;

use function Windwalker\DI\create;
use function Windwalker\ref;

return #[ConfigModule(name: 'database', enabled: true, priority: 100, belongsTo: DatabasePackage::class)]
static fn() => [

    'default' => 'local',

    'connections' => [
        'local' => [
            'factory' => ref('database.factories.instances.main'),
            'driver' => env('DATABASE_DRIVER'),
            'options' => [
                'host' => env('DATABASE_HOST') ?: 'localhost',
                'dbname' => env('DATABASE_NAME'),
                'user' => env('DATABASE_USER'),
                'password' => env('DATABASE_PASSWORD'),
                'port' => env('DATABASE_PORT'),
                'prefix' => env('DATABASE_TABLE_PREFIX'),
                'dsn' => env('DATABASE_DSN'),
            ],
            'pool' => [
                PoolInterface::MAX_SIZE => 4,
                PoolInterface::MIN_SIZE => 1,
                PoolInterface::MAX_WAIT => -1,
                PoolInterface::WAIT_TIMEOUT => -1,
                PoolInterface::IDLE_TIMEOUT => 60,
                PoolInterface::CLOSE_TIMEOUT => 3,
            ],
        ],
    ],

    'backup' => [
        'dir' => '@temp/sql-backup',
        'max' => 10,
    ],

    'providers' => [
        DatabasePackage::class,
    ],

    'listeners' => [
        ORM::class => [
            TruncateValueSubscriber::class,
        ],
    ],

    'bindings' => [
        //
    ],

    'attributes' => [
        //
    ],

    'factories' => [
        'instances' => [
            'main' => static fn(string $instanceName) => DatabaseServiceFactory::createAdapter($instanceName),
        ],
    ],
];
