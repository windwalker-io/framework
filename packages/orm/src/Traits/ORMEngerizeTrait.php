<?php

declare(strict_types=1);

namespace Windwalker\ORM\Traits;

use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Event\EnergizeEvent;
use Windwalker\ORM\ORM;

trait ORMEngerizeTrait
{
    protected ?ORM $orm {
        get => $this->retrieveMeta('orm');
    }

    protected ?EntityMapper $entityMapper {
        get => $this->retrieveMeta('entity.mapper');
    }

    #[EnergizeEvent]
    public static function ormEngerize(EnergizeEvent $event)
    {
        $event->store('orm', $event->orm);
        $event->store('entity.mapper', $event->entityMapper);
    }
}
