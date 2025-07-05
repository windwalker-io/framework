<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\QueueMessage;

/**
 * The AbstractQueueDriver class.
 *
 * @since  3.2
 */
interface QueueDriverInterface
{
    /**
     * @param  QueueMessage  $message
     *
     * @return string
     */
    public function push(QueueMessage $message): string;

    /**
     * @param  string|null  $channel
     *
     * @return QueueMessage|null
     */
    public function pop(?string $channel = null): ?QueueMessage;

    /**
     * @param  QueueMessage  $message
     *
     * @return static
     */
    public function delete(QueueMessage $message): static;

    /**
     * Release the message back to the queue, will increase attempts count.
     *
     * @param  QueueMessage  $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static;

    /**
     * Defer the message to the queue, will not increase attempts count.
     *
     * @param  QueueMessage  $message
     *
     * @return  static
     */
    public function defer(QueueMessage $message): static;
}
