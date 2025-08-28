<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\ORMOptions;

/**
 * The AfterUpdateBatchEvent class.
 */
#[Attribute]
class AfterUpdateWhereEvent extends AbstractUpdateWhereEvent
{
    public StatementInterface $statement;

    public function __construct(
        ?StatementInterface $statement = null,
        mixed $conditions = null,
        array|object $source = [],
        array $data = [],
        ORMOptions $options = new ORMOptions(),
    ) {
        if ($statement) {
            $this->statement = $statement;
        }

        parent::__construct(
            conditions: $conditions,
            source: $source,
            data: $data,
            options: $options
        );
    }
}
