<?php

declare(strict_types=1);

namespace Windwalker\Queue;

use InvalidArgumentException;
use JsonSerializable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Time\Duration;
use Windwalker\Queue\Job\JobController;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The QueueMessage class.
 *
 * @since  3.2
 */
class QueueMessage implements JsonSerializable
{
    use OptionAccessTrait;

    /**
     * Property id.
     *
     * @var  int|string
     */
    public protected(set) int|string $id = '';

    /**
     * Property attempts.
     *
     * @var  int
     */
    public protected(set) int $attempts = 0;

    /**
     * Message body from remote server.
     *
     * @var  array
     */
    public protected(set) array $body = [];

    /**
     * Message body from remote server.
     *
     * @var  string
     */
    public protected(set) string $rawBody = '';

    /**
     * Property delay.
     *
     * @var  int
     */
    public protected(set) int $delay = 0;

    public protected(set) ?\DateTimeImmutable $createdAt = null;

    /**
     * Property deleted.
     *
     * @var  bool
     */
    public protected(set) bool $deleted = false;

    protected object $unserializedJob;

    /**
     * Seconds since the message was first sent. Retries and defers do not reset it.
     */
    public int $age {
        get => $this->createdAt === null ? 0 : max(0, time() - $this->createdAt->getTimestamp());
    }

    /**
     * {@see $age} as `Time\Duration`.
     *
     * @throws  \LogicException  If `Time\Duration` is not available (requires PHP 8.6+).
     */
    public Duration $ageDuration {
        get => class_exists(Duration::class)
            ? Duration::fromSeconds($this->age)
            : throw new \LogicException('Time\\Duration requires PHP 8.6+, use $age (seconds) instead.');
    }

    /**
     * Total number of times this message has been dequeued (popped from the queue), counting both real
     * attempts ({@see $attempts}) and non-failure defers ({@see getDeferTimes()}).
     */
    public int $totalDequeues {
        get => $this->attempts + $this->getDeferTimes();
    }

    public ?string $serializedJob {
        get => $this->body['job'] ?? null;
    }

    /**
     * QueueMessage constructor.
     *
     * @param  callable|null  $job
     * @param  array          $data
     * @param  int            $delay
     * @param  array          $options
     */
    public function __construct(?callable $job = null, array $data = [], int $delay = 0, array $options = [])
    {
        if ($job !== null) {
            $this->setJob($job);
        }

        if ($data) {
            $this->setData($data);
        }

        $this->setDelay($delay);
        $this->setOptions($options);
    }

    /**
     * get
     *
     * @param  string  $name
     * @param  mixed   $default
     *
     * @return  mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->body[$name] ?? $default;
    }

    /**
     * set
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function set(string $name, mixed $value): static
    {
        $this->body[$name] = $value;

        return $this;
    }

    /**
     * Method to get property Id
     *
     * @return  int|string
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * Method to set property id
     *
     * @param  int|string  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(int|string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to get property Attempts
     *
     * @return  int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * Method to set property attempts
     *
     * @param  int  $attempts
     *
     * @return  static  Return self to support chaining.
     */
    public function setAttempts(int $attempts): static
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Method to get property Job
     *
     * @return  object
     */
    public function getJob(): object
    {
        $this->unserializeJob();

        return $this->unserializedJob;
    }

    /**
     * Method to set property job
     *
     * @param  object  $job
     *
     * @return  static  Return self to support chaining.
     */
    public function setJob(object $job): static
    {
        $this->unserializedJob = $job;

        unset($this->body['job']);

        return $this;
    }

    /**
     * Method to get property Data
     *
     * @return  array
     */
    public function getData(): array
    {
        return $this->body['data'] ?? [];
    }

    /**
     * Method to set property data
     *
     * @param  array  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(array $data): static
    {
        $this->body['data'] = $data;

        return $this;
    }

    /**
     * Method to get property Queue
     *
     * @return  string|null
     */
    public function getChannel(): ?string
    {
        return $this->body['channel'] ?? null;
    }

    /**
     * Method to set property queue
     *
     * @param  ?string  $channel
     *
     * @return  static  Return self to support chaining.
     */
    public function setChannel(?string $channel): static
    {
        $this->body['channel'] = $channel;

        return $this;
    }

    /**
     * Method to get property Body
     *
     * @return  array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * Method to set property body
     *
     * @param  array  $body
     *
     * @return  static  Return self to support chaining.
     */
    public function setBody(array $body): static
    {
        $this->body = $body;

        // Restore the creation time for drivers that do not store it separately (e.g. SQS).
        if (isset($body['createdAt']) && is_int($body['createdAt'])) {
            $this->createdAt = new \DateTimeImmutable('@' . $body['createdAt']);
        }

        return $this;
    }

    /**
     * Method to get property RawData
     *
     * @return  string
     */
    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * Method to set property rawData
     *
     * @param  string  $rawBody
     *
     * @return  static  Return self to support chaining.
     */
    public function setRawBody(string $rawBody): static
    {
        $this->rawBody = $rawBody;

        return $this;
    }

    /**
     * Method to get property Name
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->body['name'] ?? '';
    }

    /**
     * Method to set property name
     *
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(string $name): static
    {
        $this->body['name'] = $name;

        return $this;
    }

    /**
     * How many times this message has been deferred (not counted as attempts). Stored in the body.
     */
    public function getDeferTimes(): int
    {
        return (int) ($this->body['deferTimes'] ?? 0);
    }

    public function setDeferTimes(int $deferTimes): static
    {
        $this->body['deferTimes'] = max(0, $deferTimes);

        return $this;
    }

    /**
     * Method to get property Delay
     *
     * @return  int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * Method to set property delay
     *
     * @param  int  $delay
     *
     * @return  static  Return self to support chaining.
     */
    public function setDelay(int $delay): static
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * @return  array
     *
     * @throws  InvalidArgumentException
     */
    public function jsonSerialize(): array
    {
        $new = clone $this;
        $new->serializeJob();

        return $new->body;
    }

    /**
     * @return  bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param  bool  $deleted
     *
     * @return  static  Return self to support chaining.
     */
    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function serializeJob(): void
    {
        if ($this->serializedJob) {
            return;
        }

        $this->body['job'] = serialize($this->unserializedJob);
    }

    public function unserializeJob(): void
    {
        $this->unserializedJob ??= unserialize($this->body['job'] ?? '', ['allowed_classes' => true]);
    }

    public function makeJobController(
        ?\Closure $invoker = null,
        LoggerInterface $logger = new NullLogger(),
    ): JobController {
        return new JobController($this, $invoker, $logger);
    }

    /**
     * @codeCoverageIgnore  This is simple proxy method. No need to test.
     */
    public function run(
        ?\Closure $invoker = null,
        LoggerInterface $logger = new NullLogger(),
    ): JobController {
        return $this->makeJobController($invoker, $logger)->run();
    }

    public function __serialize(): array
    {
        $this->serializeJob();

        $vars = get_object_vars($this);

        // Virtual computed properties.
        unset($vars['age'], $vars['ageDuration'], $vars['totalDequeues']);

        return $vars;
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            if ($key === 'serializedJob') {
                continue;
            }

            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->unserializeJob();
    }

    public function setCreatedAt(\DateTimeImmutable|string|null $createdAt): static
    {
        if (is_string($createdAt)) {
            $createdAt = new \DateTimeImmutable($createdAt);
        }

        $this->createdAt = $createdAt;

        if ($createdAt !== null) {
            $this->body['createdAt'] = $createdAt->getTimestamp();
        } else {
            unset($this->body['createdAt']);
        }

        return $this;
    }
}
