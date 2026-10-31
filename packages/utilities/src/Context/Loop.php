<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Context;

/**
 * The LoopContext class.
 */
class Loop
{
    /**
     * @var  int
     */
    public protected(set) int $index;

    /**
     * @var  mixed
     */
    public protected(set) mixed $key;

    /**
     * @var  bool
     */
    public bool $stop = false;

    /**
     * LoopContext constructor.
     *
     * @param  int        $length
     * @param  mixed      $target
     * @param  Loop|null  $parent
     */
    public function __construct(
        public protected(set) int $length,
        public protected(set) mixed &$target,
        public protected(set) ?Loop $parent = null
    ) {}

    /**
     * loop
     *
     * @param  int    $index
     * @param  mixed  $key
     *
     * @return  static
     */
    public function loop(int $index, mixed $key): static
    {
        $this->index = $index;
        $this->key = $key;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function index(): int
    {
        return $this->index;
    }

    /**
     * Method to get property Index
     *
     * @return  mixed
     *
     *
     * @codeCoverageIgnore
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * @codeCoverageIgnore
     */
    public function length(): int
    {
        return $this->length;
    }

    public function parent(): ?Loop
    {
        return $this->parent;
    }

    public function stop(int $depth = 1): bool
    {
        $this->stop = true;

        if ($depth > 1 && $this->parent) {
            $this->parent->stop($depth - 1);
        }

        return true;
    }

    public function isStop(): bool
    {
        return $this->stop;
    }

    public function first()
    {
        return $this->target[array_key_first($this->target)];
    }

    public function last()
    {
        return $this->target[array_key_last($this->target)];
    }

    public function firstKey()
    {
        return array_key_first($this->target);
    }

    public function lastKey()
    {
        return array_key_last($this->target);
    }
}
