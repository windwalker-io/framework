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
    public StatementInterface $statement;

    public function __construct(
        ?StatementInterface $statement = null,
        mixed $conditions = null,
        ?object $entity = null,
        array $data = [],
        int $options = 0,
    ) {
        if ($statement) {
            $this->statement = $statement;
        }

        parent::__construct(
            conditions: $conditions,
            options: $options,
            entity: $entity,
            data: $data
        );
    }
}
