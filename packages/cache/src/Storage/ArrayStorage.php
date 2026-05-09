<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * Runtime Storage.
 *
 * @since 2.0
 */
class ArrayStorage implements StorageInterface, PrunableStorageInterface, GroupedStorageInterface
{
    /**
     * Property storage.
     *
     * @var  array
     */
    protected array $data = [];

    public function __construct(
        protected float $pruneProbability = 0.01,
        public protected(set) string $group = '',
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $data = $this->group === ''
            ? ($this->data[$key] ?? null)
            : ($this->data[$this->group][$key] ?? null);

        if ($data === null) {
            return null;
        }

        [$expiration, $value] = $data;

        if ($expiration !== 0 && time() > $expiration) {
            return null;
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        $exists = $this->group === ''
            ? isset($this->data[$key])
            : isset($this->data[$this->group][$key]);

        if (!$exists) {
            return false;
        }

        [$expiration] = $this->group === ''
            ? $this->data[$key]
            : $this->data[$this->group][$key];

        // expiration = 0 means "never expires" (consistent with get())
        return $expiration === 0 || time() <= $expiration;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        if ($this->group === '') {
            $this->data = [];
        } else {
            unset($this->data[$this->group]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        if ($this->group === '') {
            unset($this->data[$key]);
        } else {
            unset($this->data[$this->group][$key]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        if ($this->group === '') {
            $this->data[$key] = [$expiration, $value];
        } else {
            $this->data[$this->group][$key] = [$expiration, $value];
        }

        if ($this->shouldPrune()) {
            $this->prune();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $value = $this->get($key);

            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function prune(): int
    {
        $pruned = 0;
        $now = time();

        $groupData = $this->group === '' ? $this->data : ($this->data[$this->group] ?? []);

        foreach ($groupData as $key => [$expiration]) {
            if ($expiration !== 0 && $expiration <= $now) {
                if ($this->group === '') {
                    unset($this->data[$key]);
                } else {
                    unset($this->data[$this->group][$key]);
                }

                $pruned++;
            }
        }

        return $pruned;
    }

    public function shouldPrune(): bool
    {
        return random_int(0, 100_000) / 100_000 < $this->pruneProbability;
    }

    /**
     * Get the prune probability (0.0 to 1.0)
     */
    public function getPruneProbability(): float
    {
        return $this->pruneProbability;
    }

    /**
     * Set the prune probability (0.0 to 1.0)
     *
     * @param  float  $probability
     * @return  static  Return self to support chaining.
     */
    public function setPruneProbability(float $probability): static
    {
        $this->pruneProbability = max(0.0, min(1.0, $probability));

        return $this;
    }

    public function withGroup(string $group): static
    {
        $new = clone $this;
        $new->group = $group;

        return $new;
    }

    /**
     * Method to get property Data
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Method to set property data
     *
     * @param  array  $data
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
