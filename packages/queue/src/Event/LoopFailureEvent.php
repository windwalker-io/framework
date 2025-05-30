<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Throwable;
use Windwalker\Event\AbstractEvent;
use Windwalker\Event\BaseEvent;
use Windwalker\Queue\Worker;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * The WorkerLoopCycleFailure class.
 */
class LoopFailureEvent extends BaseEvent
{
    use AccessorBCTrait;

    public function __construct(
        public Worker $worker,
        public string $message,
        public Throwable $exception
    ) {
        //
    }
}
