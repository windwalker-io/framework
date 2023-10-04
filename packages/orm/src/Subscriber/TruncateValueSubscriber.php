<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Subscriber;

use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Event\BeforeStoreEvent;

/**
 * The TruncateValueSubscriber class.
 */
#[EventSubscriber]
class TruncateValueSubscriber
{
    #[BeforeStoreEvent]
    public function beforeStore(BeforeStoreEvent $event): void
    {
        $orm = $event->getORM();
        $metadata = $event->getMetadata();
        $data = &$event->getData();
        $columns = $orm->getDb()->getTable($metadata->getTableName())->getColumns();

        foreach ($data as $key => $datum) {
            if (!is_string($datum)) {
                continue;
            }

            $column = $columns[$key] ?? null;

            if (!$column) {
                continue;
            }

            $max = $column->getCharacterMaximumLength();

            if ($max !== null && mb_strlen($datum) > $max) {
                $data[$key] = mb_substr($datum, 0, (int) $max);
            }
        }
    }
}
