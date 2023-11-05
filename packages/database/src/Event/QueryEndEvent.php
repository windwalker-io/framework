<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Query\Query;

/**
 * The QueryEndEvent class.
 */
class QueryEndEvent extends AbstractEvent
{
    use QueryEventTrait;

    protected bool $result;

    /**
     * @return bool
     */
    public function getResult(): bool
    {
        return $this->result;
    }

    /**
     * @param  bool  $result
     *
     * @return  static  Return self to support chaining.
     */
    public function setResult(bool $result): static
    {
        $this->result = $result;

        return $this;
    }
}
