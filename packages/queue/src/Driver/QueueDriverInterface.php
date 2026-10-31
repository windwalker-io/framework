<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\QueueMessage;

/**
 * The AbstractQueueDriver class.
 *
 * Attempts contract that every driver should follow:
 *
 * - {@see pop()} counts the run that is starting: the returned message has `attempts` incremented by one, and
 *   the incremented value should be persisted (exactly once) so a crashed worker still leaves the count behind.
 * - {@see release()} is used for failures. It keeps the attempts counted by pop() (persists `$message->getAttempts()`
 *   as-is, never adds another one) and makes the message visible again after `$message->getDelay()` seconds.
 * - {@see defer()} is NOT a failure. It must undo the increment made by pop(), so the stored attempts equal the value
 *   before this run (previous failures are kept, never reset to 0), and it makes the message visible again after
 *   `$message->getDelay()` seconds.
 * - The delay is always carried by the message (`QueueMessage::setDelay()`), not passed as an argument. Callers such
 *   as `Queue::release()` / `Queue::defer()` set it before calling the driver.
 * - Drivers that do not store messages (Null, Sync) do nothing in release()/defer().
 *
 * SQS counts receives on the server (`ApproximateReceiveCount`) and cannot lower it, so it stores the attempts made
 * before each (re-)send in a message attribute, and defer() re-sends the message; see {@see SqsQueueDriver::defer()}.
 *
 * @since  3.2
 */
interface QueueDriverInterface
{
    /**
     * @param  QueueMessage  $message
     *
     * @return  string
     */
    public function push(QueueMessage $message): string;

    /**
     * @param  string|null  $channel
     *
     * @return  QueueMessage|null
     */
    public function pop(?string $channel = null): ?QueueMessage;

    /**
     * @param  QueueMessage  $message
     *
     * @return  static
     */
    public function delete(QueueMessage $message): static;

    /**
     * Release the message back to the queue after a failure. Keeps the attempts counted by pop() (does not add
     * another one) and delays visibility by `$message->getDelay()` seconds.
     *
     * @param  QueueMessage  $message
     *
     * @return  static
     */
    public function release(QueueMessage $message): static;

    /**
     * Defer the message to the queue without counting it as an attempt: undoes the increment made by pop() while
     * keeping previous attempts, and delays visibility by `$message->getDelay()` seconds.
     *
     * @param  QueueMessage  $message
     *
     * @return  static
     */
    public function defer(QueueMessage $message): static;
}
