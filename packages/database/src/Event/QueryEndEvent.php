<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Event\BaseEvent;

/**
 * The QueryEndEvent class.
 */
class QueryEndEvent extends BaseEvent
{
    use QueryEventTrait;

    public function __construct(
        public bool $result,
        mixed $query = null,
        string $sql = '',
        array $bounded = [],
        ?StatementInterface $statement = null,
    ) {
        $this->query = $query;
        $this->sql = $sql;
        $this->bounded = $bounded;
        $this->statement = $statement;
    }
}
