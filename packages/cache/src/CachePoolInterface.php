<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Windwalker\Promise\Promise;
use Windwalker\Promise\PromiseInterface;

interface CachePoolInterface extends CacheItemPoolInterface, CacheInterface, LoggerAwareInterface
{
    /** @psalm-param callable(CacheItem): mixed $handler */
    public function fetch(
        string $key,
        callable $handler,
        DateInterval|int|null $ttl = null,
        float $beta = 1.0,
        bool $lock = true,
    ): mixed;

    /** @psalm-param callable(CacheItem): mixed $handler */
    public function fetchAsync(
        string $key,
        callable $handler,
        DateInterval|int|null $ttl = null,
        float $beta = 1.0,
        bool $lock = true,
    ): PromiseInterface;

    public function withLogger(LoggerInterface $logger): static;

    public function withGroup(string $group): static;

    public function withAutoCommit(bool $autoCommit): static;

    public function getDefaultTtl(): DateInterval|int|null;

    public function withDefaultTtl(DateInterval|int|null $defaultTtl): static;
}
