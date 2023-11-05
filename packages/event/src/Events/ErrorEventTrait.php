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
    protected Throwable $exception;

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function setException(Throwable $exception): static
    {
        $this->exception = $exception;

        return $this;
    }
}
