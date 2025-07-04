<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use DateTime;
use DomainException;
use ResqueScheduler;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Resque\Resque;

/**
 * The ResqueQueueDriver class.
 *
 * @since  3.2
 */
class ResqueQueueDriver implements QueueDriverInterface
{
    public const JOB_CLASS = 'JobClass';

    /**
     * Property channel.
     *
     * @var  string
     */
    protected string $channel;

    /**
     * ResqueQueueDriver constructor.
     *
     * @param  string  $host
     * @param  int     $port
     * @param  string  $channel
     */
    public function __construct(string $host = 'localhost', int $port = 6379, string $channel = 'default')
    {
        $this->channel = $channel;

        $this->connect($host, $port);
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return string
     * @throws DomainException
     */
    public function push(QueueMessage $message): string
    {
        $channel = $message->getChannel() ?: $this->channel;

        if (!$message->getId()) {
            $message->set('attempts', 0);
            $message->set('channel', $channel);
            $message->set('id', Resque::generateJobId());
            $message->set('class', static::JOB_CLASS);
            $message->setId($message->getId());
        }

        $delay = $message->getDelay();

        $data = json_decode(json_encode($message), true);

        if ($delay > 0) {
            static::supportDelayed(true);

            ResqueScheduler::delayedPush(time() + $delay, $data);
        } else {
            Resque::push($channel, $data);
        }

        return (string) $message->getId();
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
        if (static::supportDelayed()) {
            $this->rechannelDelayedItems();
        }

        $channel = $channel ?: $this->channel;

        $job = Resque::pop($channel);

        if (!$job) {
            return null;
        }

        $message = new QueueMessage();

        $attempts = $job['attempts'];
        $attempts++;

        $message->setId($job['id']);
        $message->setBody($job);
        $message->setRawBody(json_encode($job));
        $message->setChannel($channel ?: $this->channel);
        $message->setAttempts($attempts);
        $message->set('attempts', $attempts);

        return $message;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return ResqueQueueDriver
     */
    public function delete(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        Resque::dechannel($channel, [static::JOB_CLASS => $message->getId()]);

        return $this;
    }

    /**
     * release
     *
     * @param  QueueMessage  $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static
    {
        $this->push($message);

        return $this;
    }

    /**
     * Handle delayed items for the next scheduled timestamp.
     *
     * Searches for any items that are due to be scheduled in Resque
     * and adds them to the appropriate job channel in Resque.
     */
    public function rechannelDelayedItems(): void
    {
        while (($oldestJobTimestamp = ResqueScheduler::nextDelayedTimestamp()) !== false) {
            $this->enchannelDelayedItemsForTimestamp($oldestJobTimestamp);
        }
    }

    /**
     * Schedule all of the delayed jobs for a given timestamp.
     *
     * Searches for all items for a given timestamp, pulls them off the list of
     * delayed jobs and pushes them across to Resque.
     *
     * @param  DateTime|int  $timestamp  Search for any items up to this timestamp to schedule.
     */
    public function enchannelDelayedItemsForTimestamp(DateTime|int $timestamp)
    {
        $item = null;

        while ($item = ResqueScheduler::nextItemForTimestamp($timestamp)) {
            Resque::push($item['channel'], $item);
        }
    }

    /**
     * supportDelayed
     *
     * @param  bool  $throwError
     *
     * @return bool
     * @throws DomainException
     */
    public static function supportDelayed($throwError = false): bool
    {
        if (!class_exists(ResqueScheduler::class)) {
            if ($throwError) {
                throw new DomainException(
                    'Please install chrisboulton/php-resque-scheduler to support delayed messages for Resque.'
                );
            }

            return false;
        }

        return true;
    }

    /**
     * connect
     *
     * @param  string  $host
     * @param  int     $port
     *
     * @return  void
     * @throws DomainException
     */
    public function connect(string $host, int $port): void
    {
        if (!class_exists(Resque::class)) {
            throw new DomainException('Please install chrisboulton/php-resque 1.2 to support Resque driver.');
        }

        Resque::setBackend("$host:$port");
    }
}
