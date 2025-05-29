<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;
use Windwalker\Database\Driver\StatementInterface;

/**
 * The BeforeDeleteEvent class.
 */
#[Attribute]
class AfterDeleteEvent extends AbstractDeleteEvent
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
