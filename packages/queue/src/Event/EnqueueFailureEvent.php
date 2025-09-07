<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Event\Events\ErrorEventTrait;
use Windwalker\Queue\Enqueuer;
use Windwalker\Queue\Enqueuer\EnqueuerController;
use Windwalker\Queue\Queue;

/**
 * The EnqueueFailureEvent class.
 */
class EnqueueFailureEvent extends BaseEvent
{
    use ErrorEventTrait;

    public function __construct(
        \Throwable $exception,
        public EnqueuerController $controller,
        public Enqueuer $enqueuer,
        public Queue $queue,
    ) {
        $this->exception = $exception;
    }
}
