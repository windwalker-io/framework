<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Event\BaseEvent;
use Windwalker\Query\Query;

/**
 * The HydrateEvent class.
 */
class HydrateEvent extends BaseEvent
{
    use QueryEventTrait;

    public function __construct(
        public array|object|null $item = null,
        public object|string $class = '',
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

    /**
     * @deprecated  Use property instead.
     */
    public function &getItem(): array|object|null
    {
        return $this->item;
    }
}
