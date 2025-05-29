<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;
use Windwalker\Database\Driver\StatementInterface;

/**
 * The AfterUpdateBatchEvent class.
 */
#[Attribute]
class AfterUpdateWhereEvent extends AbstractUpdateWhereEvent
{
    public function __construct(
        public StatementInterface $statement,
        mixed $conditions = null,
        int $options = 0,
        ?object $entity = null,
        array $data = []
    ) {
        parent::__construct(
            conditions: $conditions,
            options: $options,
            entity: $entity,
            data: $data
        );
    }
}
