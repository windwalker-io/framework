<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\Event\Events\ErrorEventTrait;

/**
 * The JobFailure class.
 */
class JobFailureEvent extends AbstractEvent
{
    use JobEventTrait;
    use ErrorEventTrait;
}
