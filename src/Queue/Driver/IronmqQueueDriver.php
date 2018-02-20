<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Queue\Driver;

use IronMQ\IronMQ;
use Windwalker\Queue\QueueMessage;

/**
 * The IronmqQueueDriver class.
 *
 * @since  3.2
 */
class IronmqQueueDriver implements QueueDriverInterface
{
    /**
     * Property client.
     *
     * @var  IronMQ
     */
    protected $client;

    /**
     * Property queue.
     *
     * @var  string
     */
    protected $queue;

    /**
     * IronmqQueueDriver constructor.
     *
     * @param string $projectId
     * @param string $token
     * @param string $queue
     * @param array  $options
     */
    public function __construct($projectId, $token, $queue, array $options = [])
    {
        $this->client = $this->getIronMQ($projectId, $token, $options);

        $this->queue = $queue;
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

        $options = $message->getOptions();

        $options['delay'] = $message->getDelay();

        return $this->client->postMessage($queue, json_encode($message), $options)->id;
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

        $result = $this->client->reserveMessage($queue);

        if (!$result) {
            return null;
        }

        $message = new QueueMessage;

        $message->setId($result->id);
        $message->setAttempts($result->reserved_count);
        $message->setBody(json_decode($result->body, true));
        $message->setRawBody($result->body);
        $message->setQueueName($queue ?: $this->queue);
        $message->set('reservation_id', $result->reservation_id);

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

        $this->client->deleteMessage($queue, $message->getId(), $message->get('reservation_id'));

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
        $queue = $message->getQueueName() ?: $this->queue;

        $this->client->releaseMessage(
            $queue,
            $message->getId(),
            $message->get('reservation_id'),
            $message->getDelay()
        );

        return $this;
    }

    /**
     * getIronMQ
     *
     * @param       $projectId
     * @param       $token
     * @param array $options
     *
     * @return  IronMQ
     */
    public function getIronMQ($projectId, $token, array $options)
    {
        if (!class_exists(IronMQ::class)) {
            throw new \DomainException('Please install iron-io/iron_mq first.');
        }

        $defaultOptions = [
            'project_id' => $projectId,
            'token' => $token,
        ];

        $options = array_merge($defaultOptions, $options);

        return new IronMQ($options);
    }
}
