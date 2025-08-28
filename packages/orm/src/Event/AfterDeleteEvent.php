<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\ORMOptions;

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
        ORMOptions $options = new ORMOptions(),
    ) {
        if ($statement) {
            $this->statement = $statement;
        }

        parent::__construct(
            conditions: $conditions,
            entity: $entity,
            data: $data,
            options: $options
        );
    }
}
