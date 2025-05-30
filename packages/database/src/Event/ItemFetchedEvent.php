<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Event\BaseEvent;

/**
 * The ItemFetchedEvent class.
 */
class ItemFetchedEvent extends BaseEvent
{
    use QueryEventTrait;

    public function __construct(
        public array|null $item = null,
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
    public function &getItem(): ?array
    {
        return $this->item;
    }
}
