<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
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
     * push
     *
     * @param QueueMessage $message
     *
     * @return int|string
     */
    public function push(QueueMessage $message)
    {
        $job = $message->getJob();
        /** @var JobInterface $job */
        $job = unserialize($job);

        $job->execute();

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
        return new QueueMessage;
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
}
