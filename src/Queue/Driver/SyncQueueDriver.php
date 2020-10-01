<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\Job\JobInterface;
use Windwalker\Queue\QueueMessage;

/**
 * The SyncQueueDriver class.
 *
 * @since  3.2
 */
class SyncQueueDriver implements QueueDriverInterface
{
    /**
     * @var callable
     */
    protected $handler;

    /**
     * SyncQueueDriver constructor.
     *
     * @param callable|null $handler
     */
    public function __construct(?callable $handler = null)
    {
        $this->handler = $handler ?? function (QueueMessage $message) {
            $job = unserialize($message->getJob());

            $job->execute();
        };
    }

    /**
     * push
     *
     * @param QueueMessage $message
     *
     * @return int|string
     */
    public function push(QueueMessage $message)
    {
        ($this->handler)($message);

        return 0;
    }

    /**
     * pop
     *
     * @param string $queue
     *
     * @return QueueMessage
     */
    public function pop($queue = null)
    {
        return new QueueMessage();
    }

    /**
     * delete
     *
     * @param QueueMessage|string $message
     *
     * @return static
     */
    public function delete(QueueMessage $message)
    {
        return $this;
    }

    /**
     * release
     *
     * @param QueueMessage|string $message
     *
     * @return static
     */
    public function release(QueueMessage $message)
    {
        return $this;
    }

    /**
     * Method to get property Handler
     *
     * @return  callable
     *
     * @since  3.5.22
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    /**
     * Method to set property handler
     *
     * @param callable $handler
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.22
     */
    public function setHandler(callable $handler)
    {
        $this->handler = $handler;

        return $this;
    }
}
