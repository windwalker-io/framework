<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use DomainException;
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
    protected Pheanstalk $client;

    /**
     * Property channel.
     *
     * @var  string
     */
    protected string $channel;

    /**
     * Property timeout.
     *
     * @var  int
     */
    protected int $timeout;

    /**
     * BeanstalkdQueueDriver constructor.
     *
     * @param  string  $host
     * @param  string  $channel
     * @param  int     $timeout
     */
    public function __construct(string $host, string $channel, int $timeout = 60)
    {
        $this->channel = $channel;
        $this->timeout = $timeout;

        $this->client = $this->getPheanstalk($host);
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return string
     */
    public function push(QueueMessage $message): string
    {
        $channel = $message->getChannel() ?: $this->channel;

        return (string) $this->client->useTube($channel)->put(
            json_encode($message),
            PheanstalkInterface::DEFAULT_PRIORITY,
            $message->getDelay(),
            $this->timeout
        );
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
        $channel = $channel ?: $this->channel;

        $job = $this->client->watchOnly($channel)->reserve(0);

        if (!$job instanceof Job) {
            return null;
        }

        $message = new QueueMessage();

        $message->setId($job->getId());
        $message->setAttempts((int) $this->client->statsJob($job)->reserves);
        $message->setBody(json_decode($job->getData(), true));
        $message->setRawBody($job->getData());
        $message->setChannel($channel ?: $this->channel);

        return $message;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return BeanstalkdQueueDriver
     */
    public function delete(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        $this->client->useTube($channel)->delete(new Job($message->getId(), ''));

        return $this;
    }

    /**
     * release
     *
     * @param  QueueMessage|string  $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static
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
     * @param  string  $host
     *
     * @return  Pheanstalk
     * @throws DomainException
     */
    public function getPheanstalk($host = null): Pheanstalk
    {
        if (!class_exists(Pheanstalk::class)) {
            throw new DomainException('Please install pda/pheanstalk first.');
        }

        return new Pheanstalk($host);
    }
}
