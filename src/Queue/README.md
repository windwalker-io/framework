# Windwalker Queue

Windwalker Queue is a queue manager which inspired by Laravel queue package.

This package provides an universal interface to wrap different message queue services, and a simple `Job` interface 
 to easily manage your tasks. Currently we support these drivers:

- [SQS](https://aws.amazon.com/sqs) (Amazon Simple Queue Service)
- [IronMQ](https://www.iron.io/)
- [RabbitMQ](https://www.rabbitmq.com/) (AMQP)
- [Beanstalkd](http://kr.github.io/beanstalkd/)
- [PHP Resque](https://github.com/chrisboulton/php-resque) (Redis)
- Database
- Sync (No queue, execute immediately)

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/queue": "~3.0"
    }
}
```

## Getting Started

```php
<?php

use Windwalker\Queue\Driver\SyncQueueDriver;
use Windwalker\Queue\Queue;

$queue = new Queue(new SyncQueueDriver());
```
