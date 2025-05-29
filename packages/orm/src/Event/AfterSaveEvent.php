<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;

/**
 * The BeforeSaveEvent class.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class AfterSaveEvent extends AbstractSaveEvent
{
    public function __construct(
        string $type = '',
        public object $entity = new \stdClass(),
        array|object $source = [],
        array $data = [],
        ?array $oldData = null,
        public array $fullData = [],
        int $options = 0,
        array $extra = [],
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
