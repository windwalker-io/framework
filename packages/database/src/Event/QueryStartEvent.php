<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Event\BaseEvent;

/**
 * The QueryStartEvent class.
 */
class QueryStartEvent extends BaseEvent
{
    use QueryEventTrait;

    public function __construct(
        mixed $query = null,
        string $sql = '',
        array $bounded = [],
        ?StatementInterface $statement = null,
        public ?array $params = [],
    ) {
        $this->query = $query;
        $this->sql = $sql;
        $this->bounded = $bounded;
        $this->statement = $statement;
    }
}
