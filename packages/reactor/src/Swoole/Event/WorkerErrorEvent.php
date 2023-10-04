<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The StartEvent class.
 */
class WorkerErrorEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected int $workerId;

    protected int $workerPid;

    protected int $exitCode;

    protected int $signal;

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function setWorkerId(int $workerId): static
    {
        $this->workerId = $workerId;

        return $this;
    }

    public function getWorkerPid(): int
    {
        return $this->workerPid;
    }

    public function setWorkerPid(int $workerPid): static
    {
        $this->workerPid = $workerPid;

        return $this;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function setExitCode(int $exitCode): static
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    public function getSignal(): int
    {
        return $this->signal;
    }

    public function setSignal(int $signal): static
    {
        $this->signal = $signal;

        return $this;
    }
}
