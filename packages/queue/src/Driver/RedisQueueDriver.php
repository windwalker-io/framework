<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use DomainException;
use JsonException;
use Redis;
use Windwalker\Queue\QueueMessage;

/**
 * The RedisQueueDriver class. Requires ext-redis.
 *
 * Each channel uses three keys: a list of ready jobs, a sorted set of delayed jobs (score is the available time)
 * and a sorted set of reserved jobs (score is the time when the reservation expires).
 *
 * Jobs are stored as an envelope `{id, attempts, body}`. pop() moves a job into the reserved set with the attempts
 * already incremented; a job whose reservation expires (`retry_after` seconds, the worker has died) becomes ready
 * again with the counted attempts kept. See {@see QueueDriverInterface}.
 *
 * @since  4.0
 */
class RedisQueueDriver implements QueueDriverInterface
{
    public const string RESERVED_OPTION = 'redis_reserved';

    protected ?Redis $redis = null;

    /**
     * @param  string  $defaultChannel  Default queue name.
     * @param  array   $options         `redis` (Redis instance), or host, port, password, database, timeout,
     *                                  `prefix` of keys, `retry_after` seconds before a reserved job is released.
     */
    public function __construct(protected string $defaultChannel = 'default', protected array $options = [])
    {
        if (($options['redis'] ?? null) instanceof Redis) {
            $this->redis = $options['redis'];
        }
    }

    /**
     * @throws  JsonException
     */
    public function push(QueueMessage $message): string
    {
        $id = (string) ($message->getId() ?: bin2hex(random_bytes(16)));
        $channel = $message->getChannel() ?: $this->defaultChannel;

        $envelope = $this->encodeEnvelope($id, max(0, $message->getAttempts()), $message);

        $this->requeue($channel, null, $envelope, $message->getDelay());

        return $id;
    }

    /**
     * @throws  JsonException
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        $channel = $channel ?: $this->defaultChannel;

        $reserved = $this->getRedis()->eval(
            <<<'LUA'
            local due = redis.call('zrangebyscore', KEYS[2], '-inf', ARGV[1])
            for _, job in ipairs(due) do
                redis.call('rpush', KEYS[1], job)
                redis.call('zrem', KEYS[2], job)
            end

            local expired = redis.call('zrangebyscore', KEYS[3], '-inf', ARGV[1])
            for _, job in ipairs(expired) do
                redis.call('rpush', KEYS[1], job)
                redis.call('zrem', KEYS[3], job)
            end

            local job = redis.call('lpop', KEYS[1])

            if not job then
                return false
            end

            local envelope = cjson.decode(job)
            envelope['attempts'] = envelope['attempts'] + 1
            job = cjson.encode(envelope)

            redis.call('zadd', KEYS[3], tonumber(ARGV[1]) + tonumber(ARGV[2]), job)

            return job
            LUA,
            [
                $this->key($channel),
                $this->key($channel, 'delayed'),
                $this->key($channel, 'reserved'),
                (string) time(),
                (string) $this->getRetryAfter(),
            ],
            3
        );

        if (!is_string($reserved)) {
            return null;
        }

        $envelope = json_decode($reserved, true, 512, JSON_THROW_ON_ERROR);

        $message = new QueueMessage();
        $message->setId((string) $envelope['id']);
        $message->setBody(json_decode($envelope['body'], true, 512, JSON_THROW_ON_ERROR));
        $message->setRawBody($envelope['body']);
        $message->setChannel($channel);
        $message->setAttempts((int) $envelope['attempts']);
        $message->setOption(static::RESERVED_OPTION, $reserved);

        return $message;
    }

    public function delete(QueueMessage $message): static
    {
        $reserved = $message->getOption(static::RESERVED_OPTION);

        if ($reserved === null) {
            return $this;
        }

        $channel = $message->getChannel() ?: $this->defaultChannel;

        $this->getRedis()->zRem($this->key($channel, 'reserved'), $reserved);

        $message->setOption(static::RESERVED_OPTION, null);

        return $this;
    }

    /**
     * @throws  JsonException
     */
    public function release(QueueMessage $message): static
    {
        $envelope = $this->encodeEnvelope((string) $message->getId(), $message->getAttempts(), $message);

        return $this->requeueReserved($message, $envelope);
    }

    /**
     * @throws  JsonException
     */
    public function defer(QueueMessage $message): static
    {
        $copy = clone $message;
        $copy->setDeferTimes($message->getDeferTimes() + 1);

        $envelope = $this->encodeEnvelope(
            (string) $message->getId(),
            max(0, $message->getAttempts() - 1),
            $copy
        );

        return $this->requeueReserved($message, $envelope);
    }

    /**
     * Ready, delayed and reserved jobs.
     */
    public function size(?string $channel = null): int
    {
        $channel = $channel ?: $this->defaultChannel;
        $redis = $this->getRedis();

        return (int) $redis->lLen($this->key($channel))
            + (int) $redis->zCard($this->key($channel, 'delayed'))
            + (int) $redis->zCard($this->key($channel, 'reserved'));
    }

    protected function requeueReserved(QueueMessage $message, string $envelope): static
    {
        $channel = $message->getChannel() ?: $this->defaultChannel;

        $this->requeue(
            $channel,
            $message->getOption(static::RESERVED_OPTION),
            $envelope,
            $message->getDelay()
        );

        $message->setOption(static::RESERVED_OPTION, null);

        return $this;
    }

    /**
     * Atomically removes the reserved job (if any) and adds the new envelope to the ready list or delayed set.
     */
    protected function requeue(string $channel, ?string $reserved, string $envelope, int $delay): void
    {
        $this->getRedis()->eval(
            <<<'LUA'
            if ARGV[1] ~= '' then
                redis.call('zrem', KEYS[3], ARGV[1])
            end

            if tonumber(ARGV[3]) > 0 then
                redis.call('zadd', KEYS[2], tonumber(ARGV[4]) + tonumber(ARGV[3]), ARGV[2])
            else
                redis.call('rpush', KEYS[1], ARGV[2])
            end

            return 1
            LUA,
            [
                $this->key($channel),
                $this->key($channel, 'delayed'),
                $this->key($channel, 'reserved'),
                $reserved ?? '',
                $envelope,
                (string) max(0, $delay),
                (string) time(),
            ],
            3
        );
    }

    /**
     * @throws  JsonException
     */
    protected function encodeEnvelope(string $id, int $attempts, QueueMessage $message): string
    {
        return json_encode(
            [
                'id' => $id,
                'attempts' => $attempts,
                'body' => json_encode($message, JSON_THROW_ON_ERROR),
            ],
            JSON_THROW_ON_ERROR
        );
    }

    protected function key(string $channel, string $suffix = ''): string
    {
        return ($this->options['prefix'] ?? 'queues:') . $channel . ($suffix !== '' ? ':' . $suffix : '');
    }

    protected function getRetryAfter(): int
    {
        return (int) ($this->options['retry_after'] ?? 60);
    }

    /**
     * @throws  DomainException
     */
    public function getRedis(): Redis
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(Redis::class)) {
            throw new DomainException('Please install ext-redis first.');
        }
        // @codeCoverageIgnoreEnd

        if ($this->redis === null) {
            $options = array_merge(
                ['host' => '127.0.0.1', 'port' => 6379, 'timeout' => 2.0],
                $this->options
            );

            $redis = new Redis();
            $redis->connect($options['host'], (int) $options['port'], (float) $options['timeout']);

            if (!empty($options['password'])) {
                $redis->auth($options['password']);
            }

            if (isset($options['database'])) {
                $redis->select((int) $options['database']);
            }

            $this->redis = $redis;
        }

        return $this->redis;
    }

    public function close(): void
    {
        try {
            $this->redis?->close();
        } catch (\Throwable) {
            // Already closed.
        }

        $this->redis = null;
    }
}
