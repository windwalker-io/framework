<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The StartEvent class.
 */
class WorkerErrorEvent extends AbstractEvent
{
    use ServerEventTrait;

    public int $workerId;

    public int $workerPid;

    public int $exitCode;

    public int $signal;

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
