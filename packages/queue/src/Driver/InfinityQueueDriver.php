<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\Job\ClosureJob;
use Windwalker\Queue\QueueMessage;

use function Windwalker\uid;

class InfinityQueueDriver implements QueueDriverInterface
{
    protected mixed $job;

    public function __construct(callable $job)
    {
        $this->job = new ClosureJob($job);
    }

    public function push(QueueMessage $message): string
    {
        return $message->getId() ?: '';
    }

    public function pop(?string $channel = null): ?QueueMessage
    {
        return new QueueMessage($this->job)
            ->setId(uid('mq__'));
    }

    public function delete(QueueMessage $message): static
    {
        // Do nothing, as this is an infinite queue.
        return $this;
    }

    public function release(QueueMessage $message): static
    {
        // Do nothing, as this is an infinite queue.
        return $this;
    }

    public function defer(QueueMessage $message): static
    {
        // Do nothing, as this is an infinite queue.
        return $this;
    }
}
