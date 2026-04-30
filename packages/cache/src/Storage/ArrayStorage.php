<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * Runtime Storage.
 *
 * @since 2.0
 */
class ArrayStorage implements StorageInterface, PrunableStorageInterface
{
    /**
     * Property storage.
     *
     * @var  array
     */
    protected array $data = [];

    public function __construct(protected float $pruneProbability = 0.01)
    {
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $data = $this->data[$key] ?? null;

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
        if (!isset($this->data[$key])) {
            return false;
        }

        [$expiration] = $this->data[$key];

        // expiration = 0 means "never expires" (consistent with get())
        return $expiration === 0 || time() <= $expiration;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->data = [];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        unset($this->data[$key]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        $this->data[$key] = [
            $expiration,
            $value,
        ];

        if ($this->shouldPrune()) {
            $this->prune();
        }

        return true;
    }

    public function prune(): int
    {
        $pruned = 0;
        $now = time();

        foreach ($this->data as $key => [$expiration]) {
            if ($expiration !== 0 && $expiration <= $now) {
                unset($this->data[$key]);
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
