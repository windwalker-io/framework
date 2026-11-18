> Draft: to be merged into the official docs.

## Returning Jobs from Enqueuer Handlers

An `Enqueuer` handler can return the job it wants to run instead of calling `$controller->enqueue()` itself. The
returned job is pushed to the handler's channel automatically.

### Usage

```php
use Windwalker\Queue\Enqueuer;
use Windwalker\Queue\Queue;

$enqueuer = new Enqueuer(new Queue($driver));

$enqueuer->channel('report', fn() => fn() => generateReport());

// Multiple jobs
$enqueuer->channel('mail', fn() => [
    fn() => sendMail('a@example.com'),
    fn() => sendMail('b@example.com'),
]);
```

The following return values are pushed:

- a `Closure`
- an object with an `__invoke()` method (a job class instance)
- a `QueueMessage`
- an array containing any of the above

A returned `QueueMessage` keeps its own channel, delay and options; the handler's channel is only used when the
message has no channel. Closures and job objects are pushed to the handler's channel without delay. For a delay or a
custom channel, use `$controller->enqueue($job, $delay, $channel)` instead.

### Behaviors

- Other return values (e.g. `true`, strings, numbers) are not pushed, but still count as "something returned", so
  `stopWhenEmpty` will not stop the enqueuer. Return `null` when there is nothing to do.
- The value is pushed after `AfterEnqueueEvent` is emitted, so listeners can replace `$event->result`.
- `Enqueuer::enqueue()` still returns the handler's original result, not the pushed job IDs.
