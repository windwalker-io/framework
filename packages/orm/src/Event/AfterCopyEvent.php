<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;
use Windwalker\ORM\ORMOptions;

/**
 * The AfterCopyEvent class.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class AfterCopyEvent extends AbstractSaveEvent
{
    public function __construct(
        string $type = '',
        public object $entity = new \stdClass(),
        object|array $source = [],
        array $data = [],
        ?array $oldData = null,
        public array $fullData = [],
        ORMOptions $options = new ORMOptions(),
        array $extra = [],
    ) {
        parent::__construct(
            type: $type,
            source: $source,
            data: $data,
            oldData: $oldData,
            options: $options,
            extra: $extra
        );
    }
}
