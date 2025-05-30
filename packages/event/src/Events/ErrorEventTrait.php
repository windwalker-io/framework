<?php

declare(strict_types=1);

namespace Windwalker\Event\Events;

use Throwable;

/**
 * Trait ErrorEventTrait
 */
trait ErrorEventTrait
{
    /**
     * @var Throwable
     */
    public Throwable $exception;

    /**
     * @return  Throwable
     *
     * @deprecated  Use property instead.
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @param  Throwable  $exception
     *
     * @return  $this
     *
     * @deprecated  Use property instead.
     */
    public function setException(Throwable $exception): static
    {
        $this->exception = $exception;

        return $this;
    }
}
