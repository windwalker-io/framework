> Draft: to be merged into the official docs.

## Redis Driver

`RedisQueueDriver` stores jobs in Redis, so you can run a queue without a database or a cloud service.
It requires `ext-redis`.

### Usage

```php
use Windwalker\Queue\Driver\RedisQueueDriver;
use Windwalker\Queue\Queue;

$queue = new Queue(
    new RedisQueueDriver(
        'default',
        [
            'host' => '127.0.0.1',
            'port' => 6379,
            'prefix' => 'queues:',
            'retry_after' => 60,
        ]
    )
);

$queue->push(fn() => sendEmail());
```

An existing `\Redis` connection can be reused by passing it as the `redis` option:

```php
$driver = new RedisQueueDriver('default', ['redis' => $redis]);
```

### Options

| Option        | Default     | Description                                                               |
|---------------|-------------|---------------------------------------------------------------------------|
| `redis`       |             | An existing `\Redis` instance. When set, connection options are ignored.  |
| `host`        | `127.0.0.1` | Redis host.                                                               |
| `port`        | `6379`      | Redis port.                                                               |
| `timeout`     | `2.0`       | Connect timeout in seconds.                                               |
| `password`    |             | Password passed to `auth()`.                                              |
| `database`    |             | Database index passed to `select()`.                                      |
| `prefix`      | `queues:`   | Prefix of every Redis key.                                                |
| `retry_after` | `60`        | Seconds before a reserved job is treated as lost and becomes ready again. |

### Behaviors

- Each channel uses three keys: `{prefix}{channel}` (ready list), `{prefix}{channel}:delayed` and
  `{prefix}{channel}:reserved`. Jobs are delivered in FIFO order.
- `pop()` increments the attempts and reserves the job. If the worker dies and the job is not deleted within
  `retry_after` seconds, the job becomes ready again with the counted attempts kept, so set `retry_after` longer than
  your longest job.
- `release()` and `defer()` follow the common attempts contract, and the delay carried by the message hides the job
  for that many seconds.
- `size()` counts ready, delayed and reserved jobs.
- Call `$driver->close()` to close the connection held by the driver.
