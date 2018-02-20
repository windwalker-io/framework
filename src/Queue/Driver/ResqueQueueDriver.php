<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Resque\Resque;

/**
 * The ResqueQueueDriver class.
 *
 * @since  3.2
 */
class ResqueQueueDriver implements QueueDriverInterface
{
    const JOB_CLASS = 'JobClass';

    /**
     * Property queue.
     *
     * @var  string
     */
    protected $queue;

    /**
     * ResqueQueueDriver constructor.
     *
     * @param string $host
     * @param int    $port
     * @param string $queue
     */
    public function __construct($host = 'localhost', $port = 6379, $queue = 'default')
    {
        $this->queue = $queue;

        $this->connect($host, $port);
    }

    /**
     * push
     *
     * @param QueueMessage $message
     *
     * @return int|string
     * @throws \DomainException
     */
    public function push(QueueMessage $message)
    {
        $queue = $message->getQueueName() ?: $this->queue;

        if (!$message->getId()) {
            $message->set('attempts', 0);
            $message->set('queue', $queue);
            $message->set('id', Resque::generateJobId());
            $message->set('class', static::JOB_CLASS);
            $message->setId($message->getId());
        }

        $delay = $message->getDelay();

        $data = json_decode(json_encode($message), true);

        if ($delay > 0) {
            static::supportDelayed(true);

            \ResqueScheduler::delayedPush(time() + $delay, $data);
        } else {
            Resque::push($queue, $data);
        }

        return $message->getId();
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
        if (static::supportDelayed()) {
            $this->requeueDelayedItems();
        }

        $queue = $queue ?: $this->queue;

        $job = Resque::pop($queue);

        if (!$job) {
            return null;
        }

        $message = new QueueMessage;

        $attempts = $job['attempts'];
        $attempts++;

        $message->setId($job['id']);
        $message->setBody($job);
        $message->setRawBody(json_encode($job));
        $message->setQueueName($queue ?: $this->queue);
        $message->setAttempts($attempts);
        $message->set('attempts', $attempts);

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

        Resque::dequeue($queue, [static::JOB_CLASS => $message->getId()]);

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
        $this->push($message);

        return $this;
    }

    /**
     * Handle delayed items for the next scheduled timestamp.
     *
     * Searches for any items that are due to be scheduled in Resque
     * and adds them to the appropriate job queue in Resque.
     *
     * @param \DateTime|int $timestamp Search for any items up to this timestamp to schedule.
     */
    public function requeueDelayedItems()
    {
        while (($oldestJobTimestamp = \ResqueScheduler::nextDelayedTimestamp()) !== false) {
            $this->enqueueDelayedItemsForTimestamp($oldestJobTimestamp);
        }
    }

    /**
     * Schedule all of the delayed jobs for a given timestamp.
     *
     * Searches for all items for a given timestamp, pulls them off the list of
     * delayed jobs and pushes them across to Resque.
     *
     * @param \DateTime|int $timestamp Search for any items up to this timestamp to schedule.
     */
    public function enqueueDelayedItemsForTimestamp($timestamp)
    {
        $item = null;

        while ($item = \ResqueScheduler::nextItemForTimestamp($timestamp)) {
            Resque::push($item['queue'], $item);
        }
    }

    /**
     * supportDelayed
     *
     * @param bool $throwError
     *
     * @return bool
     * @throws \DomainException
     */
    public static function supportDelayed($throwError = false)
    {
        if (!class_exists(\ResqueScheduler::class)) {
            if ($throwError) {
                throw new \DomainException('Please install chrisboulton/php-resque-scheduler to support delayed messages for Resque.');
            }

            return false;
        }

        return true;
    }

    /**
     * connect
     *
     * @param string $host
     * @param int    $port
     *
     * @return  void
     * @throws \DomainException
     */
    public function connect($host, $port)
    {
        if (!class_exists(Resque::class)) {
            throw new \DomainException('Please install chrisboulton/php-resque 1.2 to support Resque driver.');
        }

        Resque::setBackend("$host:$port");
    }
}
