<?php

declare(strict_types=1);

namespace Windwalker\Queue;

use InvalidArgumentException;
use JsonSerializable;
use Laravel\SerializableClosure\SerializableClosure;
use Windwalker\Queue\Job\JobController;
use Windwalker\Queue\Job\JobWrapperInterface;
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
    protected int|string $id = '';

    /**
     * Property attempts.
     *
     * @var  int
     */
    protected int $attempts = 0;

    /**
     * Message body from remote server.
     *
     * @var  array
     */
    protected array $body = [];

    /**
     * Message body from remote server.
     *
     * @var  string
     */
    protected string $rawBody = '';

    /**
     * Property delay.
     *
     * @var  int
     */
    protected int $delay = 0;

    /**
     * Property deleted.
     *
     * @var  bool
     */
    protected bool $deleted = false;

    public protected(set) bool $serialized = false;

    /**
     * QueueMessage constructor.
     *
     * @param callable|null $job
     * @param  array        $data
     * @param  int          $delay
     * @param  array        $options
     */
    public function __construct(?callable $job = null, array $data = [], int $delay = 0, array $options = [])
    {
        if ($job !== null) {
            $this->setRawJob($job);
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
     * @return  JobWrapperInterface
     */
    public function getRawJob(): JobWrapperInterface
    {
        $this->unserializeJob();

        return $this->body['job'];
    }

    /**
     * Method to set property job
     *
     * @param  JobWrapperInterface  $job
     *
     * @return  static  Return self to support chaining.
     */
    public function setRawJob(JobWrapperInterface $job): static
    {
        $this->serialized = false;
        $this->body['job'] = $job;

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
     * @throws InvalidArgumentException
     */
    public function jsonSerialize(): array
    {
        $new = clone $this;
        $new->serializeJob();

        return $new->body;
    }

    /**
     * @return bool
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
        if ($this->serialized) {
            return;
        }

        $this->body['job'] = serialize($this->body['job'] ?? null);

        $this->serialized = true;
    }

    public function unserializeJob(): void
    {
        if (!$this->serialized) {
            return;
        }

        $this->body['job'] = unserialize($this->body['job'] ?? '', ['allowed_classes' => true]);

        $this->serialized = false;
    }

    public function makeJobController(?\Closure $invoker = null): JobController
    {
        return new JobController($this, $invoker);
    }

    public function run(?\Closure $invoker = null): JobController
    {
        return $this->makeJobController($invoker);
    }
}
