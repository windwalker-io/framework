<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;

/**
 * The AfterCopyEvent class.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class AfterCopyEvent extends AbstractSaveEvent
{
    public function __construct(
        public object $entity,
        public array $fullData,
        string $type,
        ?array $oldData = null,
        int $options = 0,
        object|array $source = [],
        array $extra = [],
        array $data = [],
    ) {
        parent::__construct(
            type: $type,
            oldData: $oldData,
            options: $options,
            source: $source,
            extra: $extra,
            data: $data
        );
    }
}
