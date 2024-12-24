<?php

declare(strict_types=1);

use Windwalker\Core\Manager\DatabaseManager;
use Windwalker\Core\Queue\QueueManager;
use Windwalker\Queue\Driver\BeanstalkdQueueDriver;
use Windwalker\Queue\Driver\DatabaseQueueDriver;
use Windwalker\Queue\Driver\IronmqQueueDriver;
use Windwalker\Queue\Driver\RabbitmqQueueDriver;
use Windwalker\Queue\Driver\ResqueQueueDriver;
use Windwalker\Queue\Driver\SqsQueueDriver;
use Windwalker\Queue\Driver\SyncQueueDriver;
use Windwalker\Queue\Failer\DatabaseQueueFailer;
use Windwalker\Queue\Failer\NullQueueFailer;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueuePackage;

return [
    'queue' => [
        'enabled' => true,

        'default' => env('QUEUE_DEFAULT') ?: 'sync',

        'failer_default' => env('QUEUE_FAILER_DEFAULT') ?: 'database',

        'providers' => [
            QueuePackage::class
        ],

        'listeners' => [
            //
        ],

        'bindings' => [
            Queue::class => fn (QueueManager $manager) => $manager->get()
        ],

        'loop_end_scripts' => [
            //
        ],

        'job_end_scripts' => [
            //
        ],

        'factories' => [
            'instances' => [
                'sync' => fn (QueueManager $manager) => $manager->createQueue(
                    SyncQueueDriver::class,
                    handler: QueueManager::createSyncHandler()
                ),
                'sqs' => fn (QueueManager $manager) => $manager->createQueue(
                    SqsQueueDriver::class,
                    key: env('QUEUE_SQS_KEY'),
                    secret: env('QUEUE_SQS_SECRET'),
                    channel: 'default',
                    options: [
                        'region' => env('QUEUE_SQS_REGION') ?: 'us-west-2',
                        'version' => env('QUEUE_SQS_VERSION') ?: 'latest',
                    ]
                ),
                'database' => fn (QueueManager $manager, DatabaseManager $dbManager) => $manager->createQueue(
                    DatabaseQueueDriver::class,
                    db: $dbManager->get(),
                    channel: 'default',
                    table: 'queue_jobs',
                    timeout: 60
                ),
                'ironmq' => fn (QueueManager $manager) => $manager->createQueue(
                    IronmqQueueDriver::class,
                    projectId: env('QUEUE_IRONMQ_PROJECT_ID'),
                    token: env('QUEUE_IRONMQ_TOKEN'),
                    channel: 'default',
                ),
                'rabbitmq' => fn (QueueManager $manager) => $manager->createQueue(
                    RabbitmqQueueDriver::class,
                    channel: 'default',
                ),
                'beanstalkd' => fn (QueueManager $manager) => $manager->createQueue(
                    BeanstalkdQueueDriver::class,
                    host: env('QUEUE_BEANSTALKD') ?? '127.0.0.1',
                    channel: 'default',
                    timeout: 60
                ),
                'resque' => fn (QueueManager $manager) => $manager->createQueue(
                    ResqueQueueDriver::class,
                    host: env('QUEUE_RESQUE_HOST') ?? 'localhost',
                    channel: 'default',
                    port: 6379
                ),
            ],
            'failers' => [
                'database' => fn (DatabaseManager $dbManager) => new DatabaseQueueFailer(
                    db: $dbManager->get(),
                    table: 'queue_failed_jobs'
                ),
                'null' => NullQueueFailer::class
            ]
        ],
    ]
];
