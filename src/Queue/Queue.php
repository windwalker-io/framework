<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue;

use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Job\CallableJob;
use Windwalker\Queue\Job\JobInterface;

/**
 * The Queue class.
 *
 * @since  3.2
 */
class Queue
{
    /**
     * Property driver.
     *
     * @var QueueDriverInterface
     */
    protected $driver;

    /**
     * For B/C use.
     *
     * @var  callable
     * @since  3.3
     */
    protected $newInstanceHandler;

    /**
     * QueueManager constructor.
     *
     * @param QueueDriverInterface $driver
     */
    public function __construct(QueueDriverInterface $driver = null)
    {
        $this->driver = $driver;

        $this->newInstanceHandler = function ($class) {
            return new $class();
        };
    }

    /**
     * push
     *
     * @param mixed  $job
     * @param int    $delay
     * @param string $queue
     * @param array  $options
     *
     * @return  int|string
     */
    public function push($job, $delay = 0, $queue = null, array $options = [])
    {
        $message = $this->getMessageByJob($job);
        $message->setDelay($delay);
        $message->setQueueName($queue);
        $message->setOptions($options);

        return $this->driver->push($message);
    }

    /**
     * pushRaw
     *
     * @param string|array $body
     * @param int          $delay
     * @param null         $queue
     * @param array        $options
     *
     * @return  int|string
     */
    public function pushRaw($body, $delay = 0, $queue = null, array $options = [])
    {
        if (is_string($body)) {
            json_decode($body, true);
        }

        $message = new QueueMessage();
        $message->setBody($body);
        $message->setDelay($delay);
        $message->setQueueName($queue);
        $message->setOptions($options);

        return $this->driver->push($message);
    }

    /**
     * pop
     *
     * @param string $queue
     *
     * @return  QueueMessage
     */
    public function pop($queue = null)
    {
        return $this->driver->pop($queue);
    }

    /**
     * delete
     *
     * @param QueueMessage|mixed $message
     *
     * @return  void
     */
    public function delete($message)
    {
        if (!$message instanceof QueueMessage) {
            $msg = new QueueMessage();
            $msg->setId($message);
        }

        $this->driver->delete($message);

        $message->isDeleted(true);
    }

    /**
     * release
     *
     * @param QueueMessage|mixed $message
     * @param int                $delay
     *
     * @return  void
     */
    public function release($message, $delay = 0)
    {
        if (!$message instanceof QueueMessage) {
            $msg = new QueueMessage();
            $msg->setId($message);
        }

        $message->setDelay($delay);

        $this->driver->release($message);
    }

    /**
     * getMessage
     *
     * @param mixed $job
     * @param array $data
     *
     * @return QueueMessage
     * @throws \InvalidArgumentException
     */
    public function getMessageByJob($job, array $data = [])
    {
        $message = new QueueMessage();

        $job = $this->createJobInstance($job);

        $data['class'] = get_class($job);

        $message->setName($job->getName());
        $message->setJob(serialize($job));
        $message->setData($data);

        return $message;
    }

    /**
     * createJobInstance
     *
     * @param mixed $job
     *
     * @return  JobInterface
     * @throws \InvalidArgumentException
     */
    protected function createJobInstance($job)
    {
        if ($job instanceof JobInterface) {
            return $job;
        }

        // Create callable
        if (is_callable($job)) {
            $job = new CallableJob($job, md5(uniqid('', true)));
        }

        // Create by class name.
        if (is_string($job)) {
            if (!class_exists($job) || is_subclass_of($job, JobInterface::class)) {
                throw new \InvalidArgumentException(
                    sprintf('Job should be a class which implements JobInterface, %s given', $job)
                );
            }

            $handler = $this->newInstanceHandler;

            $job = $handler($job);

            if (!$job instanceof JobInterface) {
                throw new \UnexpectedValueException('Job instance is not a JobInterface.');
            }
        }

        if (is_array($job)) {
            throw new \InvalidArgumentException('Job should not be array.');
        }

        return $job;
    }

    /**
     * Method to get property Driver
     *
     * @return  QueueDriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Method to set property driver
     *
     * @param   QueueDriverInterface $driver
     *
     * @return  static  Return self to support chaining.
     */
    public function setDriver(QueueDriverInterface $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Method to get property NewInstanceHandler
     *
     * @return  callable
     *
     * @since  3.3
     */
    public function getNewInstanceHandler()
    {
        return $this->newInstanceHandler;
    }

    /**
     * Method to set property newInstanceHandler
     *
     * @param   callable $newInstanceHandler
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.3
     */
    public function setNewInstanceHandler(callable $newInstanceHandler)
    {
        $this->newInstanceHandler = $newInstanceHandler;

        return $this;
    }
}
