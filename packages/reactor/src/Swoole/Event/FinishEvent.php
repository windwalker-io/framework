<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The FinishEvent class.
 */
class FinishEvent extends AbstractEvent
{
    use ServerEventTrait;

    public int $taskId;

    public mixed $data;

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function setTaskId(int $taskId): static
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }
}
