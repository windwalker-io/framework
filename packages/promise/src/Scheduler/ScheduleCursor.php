<?php

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

/**
 * The AsyncCursor class.
 */
class ScheduleCursor
{
    /**
     * @var mixed
     */
    protected readonly mixed $cursor;

    protected bool $scheduled = false;

    /**
     * AsyncCursor constructor.
     *
     * @param  mixed  $cursor
     */
    public function __construct(mixed $cursor = null)
    {
        $this->cursor = $cursor;
    }

    /**
     * Method to get property Cursor
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function get(): mixed
    {
        return $this->cursor;
    }

    /**
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->scheduled;
    }

    /**
     * @param  bool  $scheduled
     *
     * @return  static  Return self to support chaining.
     */
    public function setScheduled(bool $scheduled): static
    {
        $this->scheduled = $scheduled;

        return $this;
    }
}
