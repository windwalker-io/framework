<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\Queue\Enqueuer;
use Windwalker\Queue\Enqueuer\EnqueuerController;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;

class AfterEnqueueEvent extends BaseEvent
{
    public function __construct(
        public EnqueuerController $controller,
        public Enqueuer $enqueuer,
        public Queue $queue,
        public mixed $result = null,
    ) {
        //
    }
}
