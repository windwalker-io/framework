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
    public StatementInterface $statement;

    public function __construct(
        ?StatementInterface $statement = null,
        mixed $conditions = null,
        int $options = 0,
        array|object $source = [],
        array $data = []
    ) {
        if ($statement) {
            $this->statement = $statement;
        }

        parent::__construct(
            conditions: $conditions,
            options: $options,
            source: $source,
            data: $data
        );
    }
}
