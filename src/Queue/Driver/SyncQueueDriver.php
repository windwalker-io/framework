<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Worker;
use Windwalker\Event\Event;
use Windwalker\Structure\Structure;

/**
 * The SyncQueueDriver class.
 *
 * @since  3.2
 */
class SyncQueueDriver implements QueueDriverInterface
{
    /**
     * Property worker.
     *
     * @var  Worker
     * @since  __DEPLOY_VERSION__
     */
    protected $worker;

    /**
     * SyncQueueDriver constructor.
     *
     * @param Worker $worker
     */
    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
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
        $this->worker->getDispatcher()->listen('onWorkerJobFailure', function (Event $event) {
            throw $event['exception'];
        });

        $this->worker->process($message, new Structure);

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
