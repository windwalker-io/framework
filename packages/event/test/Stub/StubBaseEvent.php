<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Stub;

use Windwalker\Event\BaseEvent;

/**
 * Concrete BaseEvent with declared properties, used by BaseEventTest to verify property-backed arguments.
 * Safe to remove once BaseEventTest no longer references it.
 */
class StubBaseEvent extends BaseEvent
{
    public string $flower = 'Sakura';

    public string $tree = 'Oak';
}
