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
 * The TaskEvent class.
 */
class TaskEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected int $taskId;

    protected int $srcWorkerId;

    protected mixed $data;

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function setTaskId(int $taskId): static
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function getSrcWorkerId(): int
    {
        return $this->srcWorkerId;
    }

    public function setSrcWorkerId(int $srcWorkerId): static
    {
        $this->srcWorkerId = $srcWorkerId;

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
