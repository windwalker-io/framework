<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Driver;

use Aws\Sqs\SqsClient;
use Windwalker\Queue\QueueMessage;

/**
 * The SqsQueueDriver class.
 *
 * @since  3.2
 */
class SqsQueueDriver implements QueueDriverInterface
{
    /**
     * Property client.
     *
     * @var SqsClient
     */
    protected $client;

    /**
     * Property name.
     *
     * @var string
     */
    protected $queue;

    /**
     * SqsQueueDriver constructor.
     *
     * @param string $key
     * @param string $secret
     * @param string $queue
     * @param array  $options
     */
    public function __construct($key, $secret, $queue = 'default', array $options = [])
    {
        $this->client = $this->getSqsClient($key, $secret, $options);

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
        $request = [
            'QueueUrl' => $this->getQueueUrl($message->getQueueName()),
            'MessageBody' => json_encode($message),
        ];

        $request['DelaySeconds'] = $message->getDelay();

        $options = $message->getOptions();

        $request = array_merge($request, $options);

        return $this->client->sendMessage($request)->get('MessageId');
    }

    /**
     * pop
     *
     * @param string $queue
     *
     * @return QueueMessage|null
     */
    public function pop($queue = null)
    {
        $result = $this->client->receiveMessage([
            'QueueUrl' => $this->getQueueUrl($queue),
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if ($result['Messages'] === null) {
            return null;
        }

        $data = $result['Messages'][0];

        $res = new QueueMessage;

        $res->setId($data['MessageId']);
        $res->setAttempts($data['Attributes']['ApproximateReceiveCount']);
        $res->setBody(json_decode($data['Body'], true));
        $res->setRawBody($data['Body']);
        $res->setQueueName($queue ?: $this->queue);
        $res->set('ReceiptHandle', $data['ReceiptHandle']);

        return $res;
    }

    /**
     * delete
     *
     * @param QueueMessage|string $message
     *
     * @return static
     * @internal param null $queue
     *
     */
    public function delete(QueueMessage $message)
    {
        $this->client->deleteMessage([
            'QueueUrl' => $this->getQueueUrl($message->getQueueName()),
            'ReceiptHandle' => $this->getReceiptHandle($message),
        ]);

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
        $this->client->changeMessageVisibility([
            'QueueUrl' => $this->getQueueUrl($message->getQueueName()),
            'ReceiptHandle' => $this->getReceiptHandle($message),
            'VisibilityTimeout' => $message->getDelay(),
        ]);

        return $this;
    }

    /**
     * getQueueUrl
     *
     * @param string $queue
     *
     * @return string
     */
    public function getQueueUrl($queue = null)
    {
        $queue = $queue ?: $this->queue;

        if (filter_var($queue, FILTER_VALIDATE_URL) !== false) {
            return $queue;
        }

        return $this->client->getQueueUrl(['QueueName' => $queue])->get('QueueUrl');
    }

    /**
     * getReceiptHandle
     *
     * @param QueueMessage $message
     *
     * @return  string
     */
    public function getReceiptHandle(QueueMessage $message)
    {
        return $message->get('ReceiptHandle', $message->getId());
    }

    /**
     * getSqsClient
     *
     * @param string $key
     * @param string $secret
     * @param array  $options
     *
     * @return  SqsClient
     * @throws \DomainException
     */
    public function getSqsClient($key, $secret, array $options = [])
    {
        if (!class_exists(SqsClient::class)) {
            throw new \DomainException('Please install aws/aws-sdk-php first.');
        }

        $defaultOptions = [
            'region' => 'ap-northeast-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ];

        $options = array_merge($defaultOptions, $options);

        return new SqsClient($options);
    }
}
