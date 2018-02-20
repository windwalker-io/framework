<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Queue\Driver;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Windwalker\Queue\QueueMessage;

/**
 * The RabbitmqQueueDriver class.
 *
 * @since  3.2
 */
class RabbitmqQueueDriver implements QueueDriverInterface
{
    /**
     * Property client.
     *
     * @var  AMQPStreamConnection
     */
    protected $client;

    /**
     * Property queue.
     *
     * @var  string
     */
    protected $queue;

    /**
     * Property channel.
     *
     * @var  AMQPChannel
     */
    protected $channel;

    /**
     * RabbitmqQueueDriver constructor.
     *
     * @param string $queue
     * @param array  $options
     */
    public function __construct($queue, array $options = [])
    {
        $this->client = $this->getAMQPConnection($options);

        $this->queue   = $queue;
        $this->channel = $this->client->channel();
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

        $this->queueDeclare($queue);

        $options = [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];

        if ($message->getDelay() > 0) {
            $options['application_headers'] = new AMQPTable([
                'x-delay' => $message->getDelay(),
            ]);
        }

        $msg = new AMQPMessage(json_encode($message), $options);

        $this->channel->basic_publish($msg, '', $queue);

        return 1;
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

        $this->queueDeclare($queue);

        $this->channel->basic_qos(null, 1, null);
        $result = $this->channel->basic_get($queue, false);

        if (!$result) {
            return null;
        }

        $message = new QueueMessage;

        $message->setId(0);
        $message->setBody(json_decode($result->body, true));
        $message->setRawBody($result->body);
        $message->setQueueName($queue ?: $this->queue);

        // Delivery tag
        $message->set('delivery_tag', $result->delivery_info['delivery_tag']);

        // Attempts
        $attempts = $message->get('attempts', 0) + 1;

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
        $this->channel->basic_ack($message->get('delivery_tag'));

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
        $this->delete($message);

        $message->set('delivery_tag', null);

        $this->push($message);

        return $this;
    }

    /**
     * queueDeclare
     *
     * @param string $queue
     *
     * @return  void
     */
    protected function queueDeclare($queue)
    {
        $this->channel->queue_declare($queue, false, true, false, false);
    }

    /**
     * getAMQPConnection
     *
     * @param array $options
     *
     * @return  AMQPStreamConnection
     * @throws \DomainException
     */
    public function getAMQPConnection(array $options)
    {
        if (!class_exists(AMQPStreamConnection::class)) {
            throw new \DomainException('Please install php-amqplib/php-amqplib first.');
        }

        $defaultOptions = [
            'host' => 'localhost',
            'port' => 5672,
            'user' => 'guest',
            'password' => 'guest',
        ];

        $options = array_merge($defaultOptions, $options);

        return new AMQPStreamConnection(
            $options['host'],
            $options['port'],
            $options['user'],
            $options['password']
        );
    }
}
