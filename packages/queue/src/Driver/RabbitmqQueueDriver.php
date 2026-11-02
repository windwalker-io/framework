<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use DomainException;
use JsonException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Windwalker\Queue\QueueMessage;

/**
 * The RabbitmqQueueDriver class.
 *
 * Delayed messages need no plugin: a message with a delay is published to a helper queue
 * `{channel}.delay.{seconds}s` that has a queue-level TTL and dead-letters back into the real channel.
 *
 * RabbitMQ cannot modify a stored message, so attempts made before each (re-)publish are stored in the
 * `attempts` header, and release()/defer() re-publish a copy then ack the original. See {@see QueueDriverInterface}.
 *
 * @since  3.2
 */
class RabbitmqQueueDriver implements QueueDriverInterface
{
    public const string ATTEMPTS_HEADER = 'x-windwalker-attempts';

    public const string DELIVERY_TAG_OPTION = 'rabbitmq_delivery_tag';

    protected ?AbstractConnection $connection = null;

    protected ?AMQPChannel $channel = null;

    /**
     * Helper queues used for delayed messages by this instance.
     *
     * @var  array<string, string>  Delay queue name => channel name.
     */
    protected array $delayQueues = [];

    /**
     * Delivery tags received by pop() and not yet acked, per channel.
     *
     * @var  array<string, array<int, true>>
     */
    protected array $reserved = [];

    /**
     * @param  string  $defaultChannel  Default queue name.
     * @param  array   $options         host, port, user, password, vhost, or `connection` (AbstractConnection).
     */
    public function __construct(protected string $defaultChannel = 'default', protected array $options = [])
    {
        if (isset($options['connection']) && $options['connection'] instanceof AbstractConnection) {
            $this->connection = $options['connection'];
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @throws  JsonException
     */
    public function push(QueueMessage $message): string
    {
        $id = (string) ($message->getId() ?: bin2hex(random_bytes(16)));

        $this->publish($message, $id, max(0, $message->getAttempts()));

        return $id;
    }

    public function pop(?string $channel = null): ?QueueMessage
    {
        $channel = $channel ?: $this->defaultChannel;

        $this->declareQueue($channel);

        $result = $this->getChannel()->basic_get($channel, false);

        if ($result === null) {
            return null;
        }

        $tag = (int) $result->getDeliveryTag();
        $this->reserved[$channel][$tag] = true;

        $headers = $result->has('application_headers')
            ? $result->get('application_headers')->getNativeData()
            : [];

        // Attempts before this (re-)publish + this run. A message redelivered because a worker died without
        // acking it was already run once more, so that run is counted too.
        $attempts = (int) ($headers[static::ATTEMPTS_HEADER] ?? 0) + 1 + ($result->isRedelivered() ? 1 : 0);

        $message = new QueueMessage();
        $message->setId($result->has('message_id') ? (string) $result->get('message_id') : '');
        $message->setBody(json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR));
        $message->setRawBody($result->getBody());
        $message->setChannel($channel);
        $message->setAttempts($attempts);
        $message->setOption(static::DELIVERY_TAG_OPTION, $tag);

        return $message;
    }

    public function delete(QueueMessage $message): static
    {
        $tag = $message->getOption(static::DELIVERY_TAG_OPTION);

        if ($tag === null) {
            return $this;
        }

        $channel = $message->getChannel() ?: $this->defaultChannel;

        $this->getChannel()->basic_ack((int) $tag);

        unset($this->reserved[$channel][(int) $tag]);

        $message->setOption(static::DELIVERY_TAG_OPTION, null);

        return $this;
    }

    /**
     * The copy is published first so the message is never lost if the ack fails (it may then run twice).
     */
    public function release(QueueMessage $message): static
    {
        $this->publish($message, (string) $message->getId(), $message->getAttempts());

        return $this->delete($message);
    }

    /**
     * @inheritDoc
     */
    public function defer(QueueMessage $message): static
    {
        $copy = clone $message;
        $copy->setDeferTimes($message->getDeferTimes() + 1);

        $this->publish($copy, (string) $message->getId(), max(0, $message->getAttempts() - 1));

        return $this->delete($message);
    }

    /**
     * Ready messages, delayed messages published by this driver instance and messages this instance has reserved.
     * Messages reserved by other workers are not visible to AMQP and not counted.
     */
    public function size(?string $channel = null): int
    {
        $channel = $channel ?: $this->defaultChannel;

        $size = $this->declareQueue($channel)[1] ?? 0;

        foreach ($this->delayQueues as $delayQueue => $target) {
            if ($target === $channel) {
                $size += $this->passiveCount($delayQueue);
            }
        }

        return $size + count($this->reserved[$channel] ?? []);
    }

    /**
     * @throws  JsonException
     */
    protected function publish(QueueMessage $message, string $id, int $attempts): void
    {
        $channel = $message->getChannel() ?: $this->defaultChannel;
        $delay = max(0, $message->getDelay());

        $this->declareQueue($channel);

        $target = $channel;

        if ($delay > 0) {
            $target = $this->declareDelayQueue($channel, $delay);
        }

        $amqp = new AMQPMessage(
            json_encode($message, JSON_THROW_ON_ERROR),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'message_id' => $id,
                'content_type' => 'application/json',
                'application_headers' => new AMQPTable([static::ATTEMPTS_HEADER => $attempts]),
            ]
        );

        // Default exchange routes by queue name.
        $this->getChannel()->basic_publish($amqp, '', $target);
        // Publisher confirm: the broker has accepted the message when push() returns.
        $this->getChannel()->wait_for_pending_acks();
    }

    /**
     * Declaring is idempotent, so it is repeated on every call. It also refreshes the idle timer of `x-expires`.
     */
    protected function declareQueue(string $name, array $arguments = []): array
    {
        return $this->getChannel()->queue_declare(
            $name,
            false,
            true,
            false,
            false,
            false,
            $arguments ? new AMQPTable($arguments) : []
        );
    }

    protected function declareDelayQueue(string $channel, int $delay): string
    {
        $name = "{$channel}.delay.{$delay}s";
        $ttl = $delay * 1000;

        $this->declareQueue(
            $name,
            [
                'x-message-ttl' => $ttl,
                'x-dead-letter-exchange' => '',
                'x-dead-letter-routing-key' => $channel,
                // Remove the helper queue once it is idle, it is re-declared on the next delayed publish.
                'x-expires' => $ttl + 60000,
            ]
        );

        $this->delayQueues[$name] = $channel;

        return $name;
    }

    protected function passiveCount(string $queue): int
    {
        // A failed passive declare (queue expired) closes its channel, so probe on a throwaway one.
        $probe = $this->getConnection()->channel();

        try {
            return (int) ($probe->queue_declare($queue, true)[1] ?? 0);
        } catch (AMQPProtocolChannelException) {
            unset($this->delayQueues[$queue]);

            return 0;
        } finally {
            if ($probe->is_open()) {
                $probe->close();
            }
        }
    }

    public function getChannel(): AMQPChannel
    {
        if ($this->channel === null || !$this->channel->is_open()) {
            $this->channel = $this->getConnection()->channel();
            $this->channel->confirm_select();
        }

        return $this->channel;
    }

    /**
     * @throws  DomainException
     */
    public function getConnection(): AbstractConnection
    {
        // @codeCoverageIgnoreStart
        // Optional dependency guard: unreachable while the dev dependency is installed.
        if (!class_exists(AMQPStreamConnection::class)) {
            throw new DomainException('Please install php-amqplib/php-amqplib first.');
        }
        // @codeCoverageIgnoreEnd

        if ($this->connection === null) {
            $options = array_merge(
                [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest',
                    'vhost' => '/',
                ],
                $this->options
            );

            $this->connection = new AMQPStreamConnection(
                $options['host'],
                $options['port'],
                $options['user'],
                $options['password'],
                $options['vhost']
            );
        }

        return $this->connection;
    }

    public function close(): void
    {
        try {
            $this->channel?->close();
            $this->connection?->close();
        } catch (\Throwable) {
            // Already closed.
        }

        $this->channel = null;
        $this->connection = null;
    }
}
