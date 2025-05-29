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
        public array $fullData = [],
        ?array $oldData = null,
        int $options = 0,
        array|object $source = [],
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
