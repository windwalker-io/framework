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
 * The PipeMessageEvent.php class.
 */
class PipeMessageEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected int $srcWorkerId;

    protected mixed $data;

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
