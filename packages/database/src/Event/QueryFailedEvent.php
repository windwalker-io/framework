<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Throwable;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Event\BaseEvent;
use Windwalker\Query\Query;

/**
 * The QueryFailedEvent class.
 */
class QueryFailedEvent extends BaseEvent
{
    use QueryEventTrait;

    public function __construct(
        public Throwable $exception,
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
