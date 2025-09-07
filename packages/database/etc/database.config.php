<?php

declare(strict_types=1);

namespace App\Config;

use Windwalker\Core\Attributes\ConfigModule;
use Windwalker\Core\Factory\DatabaseServiceFactory;
use Windwalker\Database\DatabasePackage;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Subscriber\TruncateValueSubscriber;
use Windwalker\Pool\Enum\Heartbeat;
use Windwalker\Pool\PoolOptions;

use function Windwalker\ref;

return #[ConfigModule(name: 'database', enabled: true, priority: 100, belongsTo: DatabasePackage::class)]
static fn() => [

    'default' => 'local',

    'connections' => [
        'local' => [
            'factory' => ref('database.factories.instances.main'),
            'driver' => env('DATABASE_DRIVER'),
            'options' => fn() => new DriverOptions(
                host: env('DATABASE_HOST') ?: 'localhost',
                dbname: env('DATABASE_NAME'),
                user: env('DATABASE_USER'),
                password: env('DATABASE_PASSWORD'),
                port: env('DATABASE_PORT'),
                prefix: env('DATABASE_TABLE_PREFIX'),
                dsn: env('DATABASE_DSN')
            ),
            'pool' => fn() => new PoolOptions(
                maxSize: 4,
                minSize: 1,
                maxWait: -1,
                maxLifetime: -1,
                waitTimeout: -1,
                idleTimeout: 60,
                closeTimeout: 3,
                heartbeat: Heartbeat::LAZY,
            ),
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
