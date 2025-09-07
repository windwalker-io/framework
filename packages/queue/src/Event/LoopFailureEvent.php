<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Throwable;
use Windwalker\Event\BaseEvent;
use Windwalker\Queue\AbstractRunner;
use Windwalker\Queue\Worker;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * The WorkerLoopCycleFailure class.
 */
class LoopFailureEvent extends BaseEvent
{
    use AccessorBCTrait;

    public function __construct(
        public AbstractRunner $runner,
        public string $message,
        public Throwable $exception
    ) {
        //
    }
}
