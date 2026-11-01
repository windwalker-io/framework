<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

interface CachePoolInterface extends CacheItemPoolInterface, CacheInterface, LoggerAwareInterface
{
    /** @psalm-param callable(CacheItem): mixed $handler */
    public function fetch(
        string $key,
        callable $handler,
        DateInterval|string|int|null $ttl = null,
        float $beta = 1.0,
        bool $lock = true,
    ): mixed;

    /**
     * Requires `windwalker/promise`. Return type is not declared so the package stays optional.
     *
     * @psalm-param callable(CacheItem): mixed $handler
     *
     * @return  \Windwalker\Promise\PromiseInterface
     */
    public function fetchAsync(
        string $key,
        callable $handler,
        DateInterval|string|int|null $ttl = null,
        float $beta = 1.0,
        bool $lock = true,
    );

    /**
     * Transform an existing value (hit only) and renew its TTL. Returns false on miss.
     *
     * @psalm-param callable(CacheItem): mixed $handler
     */
    public function modify(
        string $key,
        callable $handler,
        DateInterval|string|int|null $ttl = null,
        bool $lock = true,
    ): bool;

    /**
     * Transform the value whether it exists or not and renew its TTL. Returns the new value.
     *
     * @psalm-param callable(CacheItem): mixed $handler
     */
    public function compute(
        string $key,
        callable $handler,
        DateInterval|string|int|null $ttl = null,
        bool $lock = true,
    ): mixed;

    public function touch(string $key, DateInterval|string|int|null $ttl = null, bool $lock = true): bool;

    public function withLogger(LoggerInterface $logger): static;

    public function withGroup(string $group): static;

    public function withAutoCommit(bool $autoCommit): static;

    public function getDefaultTtl(): DateInterval|int|null;

    public function withDefaultTtl(DateInterval|string|int|null $defaultTtl): static;
}
