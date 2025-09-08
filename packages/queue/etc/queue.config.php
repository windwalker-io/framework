<?php

declare(strict_types=1);

namespace App\Config;

use Windwalker\Core\Attributes\ConfigModule;
use Windwalker\Core\Factory\DatabaseServiceFactory;
use Windwalker\Core\Queue\QueueFactory;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Queue\Enum\DatabaseIdType;
use Windwalker\Queue\Failer\DatabaseQueueFailer;
use Windwalker\Queue\QueuePackage;

return #[ConfigModule(name: 'queue', enabled: true, priority: 100, belongsTo: QueuePackage::class)]
static fn() => [

    'default' => env('QUEUE_DEFAULT') ?: 'sync',

    'failer_default' => env('QUEUE_FAILER_DEFAULT') ?: 'database',

    'providers' => [
        QueuePackage::class,
    ],

    'listeners' => [
        //
    ],

    'bindings' => [
        //
    ],

    'loop_end_scripts' => [
        //
    ],

    'job_end_scripts' => [
        //
    ],

    'enqueuer' => [
        'handlers' => [
            __DIR__ . '/../../resources/registry/enqueuers.php',
        ],
        'loop_end_scripts' => [
            //
        ],
    ],

    'factories' => [
        'instances' => [
            'sync' => static fn() => QueueFactory::syncAdapter(
                handler: QueueFactory::createSyncHandler()
            ),
            'sqs' => static fn() => QueueFactory::sqsAdapter(
                key: env('QUEUE_SQS_KEY'),
                secret: env('QUEUE_SQS_SECRET'),
                channel: 'default',
                options: [
                    'region' => env('QUEUE_SQS_REGION') ?: 'us-west-2',
                    'version' => env('QUEUE_SQS_VERSION') ?: 'latest',
                ]
            ),
            'database' => static fn(DatabaseAdapter $db) => QueueFactory::databaseAdapter(
                db: $db,
                channel: 'default',
                table: 'queue_jobs',
                timeout: 60,
                idType: DatabaseIdType::UUID_BIN
            ),
        ],
        'failers' => [
            'database' => static fn(DatabaseServiceFactory $factory) => new DatabaseQueueFailer(
                db: $factory->get(),
                table: 'queue_failed_jobs',
                idType: DatabaseIdType::UUID_BIN
            ),
        ],
    ],
];
