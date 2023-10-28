<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

/**
 * Trait WorkerEventTrait
 */
trait WorkerEventTrait
{
    protected int $workerId;

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @param  int  $workerId
     *
     * @return  static  Return self to support chaining.
     */
    public function setWorkerId(int $workerId): static
    {
        $this->workerId = $workerId;

        return $this;
    }
}
