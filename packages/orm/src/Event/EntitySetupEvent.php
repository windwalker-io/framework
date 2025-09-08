<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Event\BaseEvent;
use Windwalker\ORM\Metadata\EntityMetadata;

class EntitySetupEvent extends BaseEvent
{
    public function __construct(
        public EntityMetadata $metadata,
    ) {
    }
}
