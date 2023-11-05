<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Throwable;
use Windwalker\Event\AbstractEvent;
use Windwalker\Query\Query;

/**
 * The QueryFailedEvent class.
 */
class QueryFailedEvent extends AbstractEvent
{
    use QueryEventTrait;

    protected Throwable $exception;

    /**
     * @return Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @param  Throwable  $exception
     *
     * @return  static  Return self to support chaining.
     */
    public function setException(Throwable $exception): static
    {
        $this->exception = $exception;

        return $this;
    }
}
