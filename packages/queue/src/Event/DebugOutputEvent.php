<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Psr\Log\LogLevel;
use Windwalker\Event\BaseEvent;

class DebugOutputEvent extends BaseEvent
{
    public function __construct(
        public string $level = LogLevel::DEBUG,
        public string $message = '',
        public array $context = [],
    ) {
    }
}
