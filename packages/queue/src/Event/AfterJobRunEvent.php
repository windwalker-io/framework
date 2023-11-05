<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The AfterJobRunEvent class.
 */
class AfterJobRunEvent extends AbstractEvent
{
    use JobEventTrait;
}
