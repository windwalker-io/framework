<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Aws\Sqs\SqsClient;
use DomainException;
use JsonException;
use Windwalker\Queue\QueueMessage;

/**
 * The SqsQueueDriver class.
 *
 * @since  3.2
 */
class SqsQueueDriver implements QueueDriverInterface
{
    public const string ATTEMPTS_ATTRIBUTE = 'WindwalkerAttempts';

    /**
     * Property client.
     *
     * @var  SqsClient
     */
    protected SqsClient $client;

    /**
     * Property name.
     *
     * @var  string
     */
    protected string $channel;

    /**
     * SqsQueueDriver constructor.
     *
     * @param  string  $key
     * @param  string  $secret
     * @param  string  $channel
     * @param  array   $options
     */
    public function __construct(string $key, string $secret, string $channel = 'default', array $options = [])
    {
        $this->client = $this->getSqsClient($key, $secret, $options);

        $this->channel = $channel;
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return  string
     * @throws  JsonException
     */
    public function push(QueueMessage $message): string
    {
        $channel = $message->getChannel() ?: $this->channel;

        $request = [
            'QueueUrl' => $this->getQueueUrl($channel),
            'MessageBody' => json_encode($message, JSON_THROW_ON_ERROR),
        ];

        $options = $message->getOptions();
        $isFifo = str_ends_with($channel, '.fifo');

        // FIFO queues only support a queue-level delay.
        if (!$isFifo) {
            $request['DelaySeconds'] = $message->getDelay();
        }

        if ($isFifo) {
            $options['MessageGroupId'] ??= '1';
            // A re-sent (deferred) body would be dropped as a duplicate within the dedup window otherwise.
            $options['MessageDeduplicationId'] ??= bin2hex(random_bytes(16));
        }

        // SQS counts receives on the server and cannot be reset, so attempts made before this send are stored
        // on the message itself. See pop().
        $options['MessageAttributes'][static::ATTEMPTS_ATTRIBUTE] ??= [
            'DataType' => 'Number',
            'StringValue' => (string) max(0, $message->getAttempts()),
        ];

        $request = array_merge($request, $options);

        return (string) $this->client->sendMessage($request)->get('MessageId');
    }

    /**
     * pop
     *
     * @param  string|null  $channel
     *
     * @return  QueueMessage|null
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        $result = $this->client->receiveMessage(
            [
                'QueueUrl' => $this->getQueueUrl($channel),
                'AttributeNames' => ['ApproximateReceiveCount'],
                'MessageAttributeNames' => [static::ATTEMPTS_ATTRIBUTE],
            ]
        );

        if (empty($result['Messages'])) {
            return null;
        }

        $data = $result['Messages'][0];

        $message = new QueueMessage();

        $message->setId($data['MessageId']);
        // Attempts before this (re-)send + receives since then. Matches the contract in QueueDriverInterface.
        $message->setAttempts(
            (int) ($data['MessageAttributes'][static::ATTEMPTS_ATTRIBUTE]['StringValue'] ?? 0)
            + (int) $data['Attributes']['ApproximateReceiveCount']
        );
        $message->setBody(json_decode($data['Body'], true));
        $message->setRawBody($data['Body']);
        $message->setChannel($channel ?: $this->channel);
        $message->set('ReceiptHandle', $data['ReceiptHandle']);

        return $message;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return  static
     */
    public function delete(QueueMessage $message): static
    {
        $this->client->deleteMessage(
            [
                'QueueUrl' => $this->getQueueUrl($message->getChannel()),
                'ReceiptHandle' => $this->getReceiptHandle($message),
            ]
        );

        return $this;
    }

    /**
     * @param  QueueMessage  $message
     *
     * @return  static
     */
    public function release(QueueMessage $message): static
    {
        $this->client->changeMessageVisibility(
            [
                'QueueUrl' => $this->getQueueUrl($message->getChannel()),
                'ReceiptHandle' => $this->getReceiptHandle($message),
                'VisibilityTimeout' => $message->getDelay(),
            ]
        );

        return $this;
    }

    /**
     * SQS cannot lower `ApproximateReceiveCount`, so defer re-sends the message with a delay and the attempts that
     * were counted before this run (the increment made by pop() is undone), then removes the original.
     * The new copy is sent first so the message is never lost if the delete fails (it may then run twice).
     *
     * @inheritDoc
     */
    public function defer(QueueMessage $message): static
    {
        $copy = clone $message;
        $copy->setAttempts(max(0, $message->getAttempts() - 1));

        $this->push($copy);

        return $this->delete($message);
    }

    /**
     * getQueueUrl
     *
     * @param  string|null  $channel
     *
     * @return  string
     */
    public function getQueueUrl(?string $channel = null): string
    {
        $channel = $channel ?: $this->channel;

        if (filter_var($channel, FILTER_VALIDATE_URL) !== false) {
            return $channel;
        }

        return $this->client->getQueueUrl(['QueueName' => $channel])->get('QueueUrl');
    }

    /**
     * getReceiptHandle
     *
     * @param  QueueMessage  $message
     *
     * @return  string
     */
    public function getReceiptHandle(QueueMessage $message): string
    {
        return $message->get('ReceiptHandle', $message->getId());
    }

    /**
     * getSqsClient
     *
     * @param  string  $key
     * @param  string  $secret
     * @param  array   $options
     *
     * @return  SqsClient
     * @throws  DomainException
     */
    public function getSqsClient(string $key, string $secret, array $options = []): SqsClient
    {
        // @codeCoverageIgnoreStart
        // Optional dependency guard: unreachable while the dev dependency is installed.
        if (!class_exists(SqsClient::class)) {
            throw new DomainException('Please install aws/aws-sdk-php first.');
        }
        // @codeCoverageIgnoreEnd

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
