<?php

declare(strict_types=1);

namespace Windwalker\Queue;

use InvalidArgumentException;
use JsonException;
use Windwalker\DI\Definition\DefinitionInterface;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Job\ClosureJob;
use Windwalker\Queue\Job\JobWrapper;
use Windwalker\Queue\Job\JobWrapperInterface;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;

/**
 * The Queue class.
 *
 * @since  3.2
 */
class Queue
{
    use ObjectBuilderAwareTrait;

    /**
     * Property driver.
     *
     * @var QueueDriverInterface
     */
    protected QueueDriverInterface $driver;

    /**
     * QueueManager constructor.
     *
     * @param  QueueDriverInterface  $driver
     */
    public function __construct(QueueDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * push
     *
     * @param  mixed        $job
     * @param  int          $delay
     * @param  string|null  $channel
     * @param  array        $options
     *
     * @return int|string
     */
    public function push(mixed $job, int $delay = 0, ?string $channel = null, array $options = []): int|string
    {
        $message = $this->getMessageByJob($job);
        $message->setDelay($delay);
        $message->setChannel($channel);
        $message->setOptions($options);

        return $this->driver->push($message);
    }

    /**
     * pushRaw
     *
     * @param  string|array  $body
     * @param  int           $delay
     * @param  string|null   $channel
     * @param  array         $options
     *
     * @return  int|string
     * @throws JsonException
     */
    public function pushRaw(
        string|array $body,
        int $delay = 0,
        ?string $channel = null,
        array $options = []
    ): int|string {
        if (is_string($body)) {
            json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        }

        $message = new QueueMessage();
        $message->setBody($body);
        $message->setDelay($delay);
        $message->setChannel($channel);
        $message->setOptions($options);

        return $this->driver->push($message);
    }

    /**
     * pop
     *
     * @param  string|null  $channel
     *
     * @return QueueMessage|null
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        return $this->driver->pop($channel);
    }

    /**
     * delete
     *
     * @param  QueueMessage|mixed  $message
     *
     * @return  void
     */
    public function delete(mixed $message): void
    {
        if (!$message instanceof QueueMessage) {
            $msg = new QueueMessage();
            $msg->setId($message);

            $message = $msg;
        }

        $this->driver->delete($message);

        $message->setDeleted(true);
    }

    /**
     * @param  QueueMessage|mixed  $message
     * @param  int                 $delay
     *
     * @return  void
     */
    public function release(mixed $message, int $delay = 0): void
    {
        if (!$message instanceof QueueMessage) {
            $msg = new QueueMessage();
            $msg->setId($message);
        }

        $message->setDelay($delay);

        $this->driver->release($message);
    }

    public function defer(mixed $message, int $delay = 0): void
    {
        if (!$message instanceof QueueMessage) {
            $msg = new QueueMessage();
            $msg->setId($message);
        }

        $message->setDelay($delay);

        $this->driver->defer($message);
    }

    /**
     * getMessage
     *
     * @param  mixed  $job
     * @param  array  $data
     *
     * @return QueueMessage
     * @throws InvalidArgumentException
     */
    public function getMessageByJob(mixed $job, array $data = []): QueueMessage
    {
        $message = new QueueMessage();

        $job = $this->createJobInstance($job);

        $data['class'] = $job::class;

        $message->setName(get_debug_type($job));
        $message->setJob($job)->serializeJob();
        $message->setData($data);

        return $message;
    }

    /**
     * @param  object|callable|string  $job
     *
     * @return  object
     */
    protected function createJobInstance(mixed $job): object
    {
        // Create callable
        if ($job instanceof \Closure) {
            $instance = new ClosureJob($job);
        } elseif (is_string($job)) {
            // Create by class name.
            if (!class_exists($job) || method_exists($job, '__invoke')) {
                throw new InvalidArgumentException(
                    'Job should be a class which has __invoke() method.'
                );
            }

            $instance = $this->createJobByClassName($job);
        } elseif ($job instanceof DefinitionInterface) {
            $instance = $this->createJobByClassName($job);
        } else {
            $instance = $job;
        }

        return $instance;
    }

    /**
     * Method to get property Driver
     *
     * @return  QueueDriverInterface
     */
    public function getDriver(): QueueDriverInterface
    {
        return $this->driver;
    }

    /**
     * Method to set property driver
     *
     * @param  QueueDriverInterface  $driver
     *
     * @return  static  Return self to support chaining.
     */
    public function setDriver(QueueDriverInterface $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * createJobByClassName
     *
     * @param  mixed  $job      Can be class name or DI definition.
     * @param  mixed  ...$args  Arguments.
     *
     * @return  object
     */
    protected function createJobByClassName(mixed $job, ...$args): object
    {
        return $this->getObjectBuilder()->getBuilder()($job, ...$args);
    }
}
