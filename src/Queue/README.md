# Windwalker Queue

Windwalker Queue is a queue manager which inspired by Laravel queue package.

This package provides an universal interface to wrap different message queue services, and a simple `Job` interface 
 to easily manage your tasks. Currently we support these drivers:

- [SQS](https://aws.amazon.com/sqs) (Amazon Simple Queue Service)
- [IronMQ](https://www.iron.io/)
- [RabbitMQ](https://www.rabbitmq.com/) (AMQP)
- [Beanstalkd](http://kr.github.io/beanstalkd/)
- [PHP Resque](https://github.com/chrisboulton/php-resque) (Redis)
- Pdo (MySQL)
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

Create a new Queue instance with AWS SQS driver, remember send AWS key and secret into it.

```php
<?php

use Windwalker\Queue\Queue;

$queue = new Queue(new \Windwalker\Queue\Driver\SqsQueueDriver($accessKey, $secret));
```

### Create A Job

You task or logic must wrap in a `JobInterface` instance:

```php
class HelloJob implements \Windwalker\Queue\Job\JobInterface
{
    public function getName()
    {
        return 'hello';
    }

    public function execute()
    {
        Mailer::send($message);
    }
}
```

Then push it to queue:

```php
// Push to `default` queue.
$queue->push(new HelloJob());

// Delay 5 seconds
$queue->push(new HelloJob(), 5);

// Push to anoter queue
$queue->push(new HelloJob(), 0, 'flower');
```

### Add More Information to Job

Sometimes you need more information to handle things, add them to constructor:

```php
class HelloJob implements JobInterface
{
    protected $url;
    protected $path;
    protected $size;
    protected $crop;

    public function __construct($url, $path, $size = 600, $crop = true)
    {
        $this->url = $url;
        $this->path = $path;
        $this->size = $size;
        $this->crop = $crop;
    }

    public function getName()
    {
        return 'hello';
    }

    public function execute()
    {
        $imgData = (new HttpClient)->get($this->url);

        ImageHelper::load($imgData)
            ->resize($this->size, $this->size, $this->crop)
            ->save($this->path)
    }
}
```

Then inject these information when you creating Jobs:

```php
$queue->push(
    new HelloJob(
        'http://example/image.jpg',
        __DIR__ . '/../images/image.jpg',
        400,
        true
    )
);
```

Then Let's run Worker in CLI:

```php
use Windwalker\Event\Dispatcher;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

$worker = new Worker(new Queue($driver), new Dispatcher()); 

// Run once
$worker->runNextJob(['default', 'flower']);

// Or run as deamon
$worker->loop(['default', 'flower']);
```

## Use Queue Object

### Push

Simple use push to add a job object as new message:

```php
$queue->push(new MyJob($data));
```

Push message but wait for 10 seconds later to run:

```php
$queue->push(new MyJob($data), 10);
```

Push to directly queue:

```php
$queue->push(new MyJob($data), 0, 'flower');
```

Push raw data instead job object:

```php
$queue->pushRaw(['flower' => 'sakura'], 0, 'flower');
```

### Pop, Delete and Release

Use `pop()` to get next message:

```php
$message = $queue->pop(); // QueueMessage object

$message->getJob();
$message->getBody();
$message->getRawBody();
$message->getId();
$message->getAttempts();
$message->get('flower'); // Get data from body
```

Delete a message:

```php
$queue->delete($message);

// argument should be a QueueMessage object
$message = new \Windwalker\Core\Queue\QueueMessage;
$message->setId($id);

$queue->delete($message);

// You can delete by ID
$queue->delete($id);

// Check this message deleted
$message->isDeleted();
```

Release back to queue list (attempts will auto +1):

```php
$queue->release($message);

// You can release by ID
$queue->release($id);

// Wait a while to run again:
$queue->release($message, 15);
```

## Use Worker

`Worker` will auto fetch new job to run in the background, you can integrate it to your command line programs like 
Symfony Console or your own CLI system.

Create Worker instance:

```php
use Windwalker\Event\Dispatcher;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

$worker = new Worker(new Queue($driver), new Dispatcher());

// You can set PSR-3 Logger into it.
$worker = new Worker(new Queue($driver), new Dispatcher(), new MyLogger);
```

We recommend that you should prepare a Logger to log all queue messages then you can easily debug and know worker information.

### Options

There are many options that you can configure it, you must wrap options array by a `Structure` object:

```php
use Windwalker\Structure\Structure;

$options = [
    'timeout' => 30, // Number of seconds that a job can run.
    'delay' => 0, // Delay time for failed job to wait next run.
    'force' => false, // Force run worker if in pause mode.
    'memory' => 128, // The memory limit in megabytes.
    'sleep' => 1, // Number of seconds to sleep after job run complete.
    'tries' => 5, // Number of times to attempt a job if it failed.
    'restart_signal' => '/path/to/restart_signal_file', // Restart signal
];

$worker->loop(['default', 'flower'], new Structure($options));
```

### Pause Mode and `force` option

You can pause Worker by your CLI process:

```php
if (fil_get_content('/mode') === 'offline') {
    $worker->setState(Worker::STATE_PAUSE);
} else {
    $worker->setState(Worker::STATE_ACTIVE);
}
```

In pause state, Worker will still run but won't execute any jobs, but if you set `force` option to TRUE, it will 
ignore pause state and still run jobs.

### Restart Signals

Sometimes you code updated and you wish all background Workers can restart to use new code. You can prepare a file with 
timestamp.

Write restart signal

```php
<?php

$date = new DateTime('now');

file_put_contents('/path/to/restart_signal_file', $date->format('U'));
```

Then if you set `restart_signal` as `/path/to/restart_signal_file`, if Worker found it's start time less than restart time,
it will auto stop process. The the daemon watcher you set in the system will auto raise a new Worker.

### Listen to Workers

Works has some events that you can get messages and print to terminal or write logs.

```php
use Windwalker\Event\Event;

$worker->getDispatcher()
    ->listen('onWorkerBeforeJobRun', function (Event $event) {
        $job     = $event['job'];
        $message = $event['message'];

        // Print to terminal
        echo sprintf(
            'Run Job: <info>%s</info> - Message ID: <info>%s</info>',
            $job->getName(),
            $message->getId()
        );
    })
    ->listen('onWorkerJobFailure', function (Event $event) {
        $job     = $event['job'];
        $e       = $event['exception'];
        $message = $event['message'];

        // Print to terminal
        echo sprintf(
            'Job %s failed: %s (%s)',
            $job->getName(),
            $e->getMessage(),
            $message->getId()
        );

        // If be deleted, send to failed table
        if ($message->isDeleted()) {
            $failer->add(
                $this->console->get('queue.connection', 'sync'),
                $message->getQueueName(),
                json_encode($message),
                (string) $e
            );
        }
    })
    ->listen('onWorkerLoopCycleStart', function (Event $event) {
        /** @var Worker $worker */
        $worker = $event['worker'];

        switch ($worker->getState()) {
            case $worker::STATE_ACTIVE:
                if ($this->console->isOffline()) {
                    $worker->setState($worker::STATE_PAUSE);
                }
                break;

            case $worker::STATE_PAUSE:
                if ($this->console->isOffline()) {
                    $worker->setState($worker::STATE_ACTIVE);
                }
                break;
        }
    })
    ->listen('onWorkerLoopCycleFailure', function (Event $event) {
        /** @var \Exception $e */
        $e = $event['exception'];

        // Print to terminal
        echo sprintf(
            '%s File: %s (%s)',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    })
    ->listen('onWorkerLoopCycleEnd', function (Event $event) {
        //
    });
```

Available events:

- onWorkerLoopCycleStart
- onWorkerLoopCycleFailure
- onWorkerLoopCycleEnd
- onWorkerBeforeJobRun
- onWorkerAfterJobRun
- onWorkerStop
- onWorkerJobFailure

## Drivers

### Supported Driver

```php
use Windwalker\Queue\Queue;

// AWS SQS
$driver = new \Windwalker\Queue\Driver\SqsQueueDriver($key, $secret, 'default');

// IronMQ
$driver = new \Windwalker\Queue\Driver\IronmqQueueDriver($projectId, $token, 'default');

// RabbitMQ
$driver = new \Windwalker\Queue\Driver\RabbitmqQueueDriver('default');

// Beanstalkd
$driver = new \Windwalker\Queue\Driver\BeanstalkdQueueDriver('127.0.0.1', 'default');

// PHP Resque (Redis)
$driver = new \Windwalker\Queue\Driver\BeanstalkdQueueDriver('127.0.0.1', 'default');

// PDO
$driver = new \Windwalker\Queue\Driver\PdoQueueDriver($pdo, 'default', 'queue_jobs'/* table name*/);
``` 

### PDO Driver

You must create a table to handle queue, here is an example [SQL file](Resources/sql/queue_jobs.sql).

## Failed Jobs

If jobs failed, you may want to log them in a place and retry later or check the error message. Windwalker provides 
a database driven failed handler. Please copy [SQL file](Resources/sql/queue_failed_jobs.sql) here.

Now you must log failed jobs in Worker failure events.

```php
use Windwalker\Event\Event;

$failer = new \Windwalker\Queue\Failer\PdoQueueFailer($pdo); 

$worker->getDispatcher()
    ->listen('onWorkerJobFailure', function (Event $event) use ($failer) {
        $job     = $event['job'];
        $e       = $event['exception'];
        $message = $event['message'];

        // Print message to terminal...

        // If be deleted, send to failed table
        if ($message->isDeleted()) {
            $failer->add(
                'pdo',
                $message->getQueueName(),
                json_encode($message),
                (string) $e
            );
        }
    });
```

Use your CLI program to send failed jobs back to queue.

```php
// In CLI

$fails = $failer->all();

foreach ($fails as $failed) {
    $queue->pushRaw(json_decode($failed['body'], true), 5, $failed['queue']);
}
```

More examples, please see: https://windwalker.io/documentation/3.x/services/queue.html#failed-jobs
