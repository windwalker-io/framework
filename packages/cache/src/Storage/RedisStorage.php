<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use Redis;

/**
 * The RedisStorage class.
 */
class RedisStorage implements StorageInterface, GroupedStorageInterface
{
    /**
     * Property defaultHost.
     *
     * @var  string
     */
    protected string $defaultHost = '127.0.0.1';

    /**
     * Property defaultPort.
     *
     * @var  int
     */
    protected int $defaultPort = 6379;

    /**
     * RedisStorage constructor.
     *
     * @param  Redis|null  $driver
     */
    public function __construct(protected ?Redis $driver = null, public protected(set) string $group = '')
    {
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $this->connect();

        $value = $this->driver->get($this->normalizeKey($key));

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        $this->connect();

        return (bool) $this->driver->exists($this->normalizeKey($key));
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->connect();

        if ($this->group !== '') {
            $keys = $this->driver->keys($this->group . ':*');

            if (!is_array($keys) || $keys === []) {
                return true;
            }

            foreach ($keys as $key) {
                $this->driver->del($key);
            }

            return true;
        }

        return $this->driver->flushall();
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        $this->connect();

        $this->driver->del($this->normalizeKey($key));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        $this->connect();

        $normalizedKey = $this->normalizeKey($key);

        if (!$this->driver->set($normalizedKey, $value)) {
            return false;
        }

        if ($expiration !== 0) {
            $ttl = $expiration - time();

            $this->driver->expire($normalizedKey, $ttl);
        }

        return true;
    }

    /**
     * connect
     *
     * @return  static
     */
    protected function connect(): static
    {
        // We want to only create the driver once.
        if (isset($this->driver)) {
            return $this;
        }

        $this->driver = new Redis();

        if (($this->defaultHost === 'localhost' || filter_var($this->defaultHost, FILTER_VALIDATE_IP))) {
            $this->driver->connect($this->defaultHost, $this->defaultPort);
        } else {
            $this->driver->connect($this->defaultHost, null);
        }

        return $this;
    }

    public function withGroup(string $group): static
    {
        $new = clone $this;
        $new->group = $group;

        return $new;
    }

    protected function normalizeKey(string $key): string
    {
        if ($this->group === '') {
            return $key;
        }

        return $this->group . ':' . $key;
    }
}
