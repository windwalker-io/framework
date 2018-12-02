<?php
/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Driver;

use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Windwalker\Queue\QueueMessage;

/**
 * The BeanstalkdQueueDriver class.
 *
 * @since  3.2
 */
class BeanstalkdQueueDriver implements QueueDriverInterface
{
    /**
     * Property client.
     *
     * @var  Pheanstalk
     */
    protected $client;

    /**
     * Property queue.
     *
     * @var  string
     */
    protected $queue;

    /**
     * Property timeout.
     *
     * @var  int
     */
    protected $timeout;

    /**
     * BeanstalkdQueueDriver constructor.
     *
     * @param string $host
     * @param string $queue
     * @param int    $timeout
     */
    public function __construct($host, $queue, $timeout = 60)
    {
        $this->queue = $queue;
        $this->timeout = $timeout;

        $this->client = $this->getPheanstalk($host);
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
        $queue = $message->getQueueName() ?: $this->queue;

        return $this->client->useTube($queue)->put(
            json_encode($message),
            PheanstalkInterface::DEFAULT_PRIORITY,
            $message->getDelay(),
            $this->timeout
        );
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
        $queue = $queue ?: $this->queue;

        $job = $this->client->watchOnly($queue)->reserve(0);

        if (!$job instanceof Job) {
            return null;
        }

        $message = new QueueMessage();

        $message->setId($job->getId());
        $message->setAttempts($this->client->statsJob($job)->reserves);
        $message->setBody(json_decode($job->getData(), true));
        $message->setRawBody($job->getData());
        $message->setQueueName($queue ?: $this->queue);

        return $message;
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
        $queue = $message->getQueueName() ?: $this->queue;

        $this->client->useTube($queue)->delete(new Job($message->getId(), ''));

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
        $this->client->release(
            new Job($message->getId(), ''),
            PheanstalkInterface::DEFAULT_PRIORITY,
            $message->getDelay()
        );

        return $this;
    }

    /**
     * getPheanstalk
     *
     * @param string $host
     *
     * @return  Pheanstalk
     * @throws \DomainException
     */
    public function getPheanstalk($host = null)
    {
        if (!class_exists(Pheanstalk::class)) {
            throw new \DomainException('Please install pda/pheanstalk first.');
        }

        return new Pheanstalk($host);
    }
}
