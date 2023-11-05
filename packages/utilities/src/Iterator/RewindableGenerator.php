<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use Generator;
use Iterator;
use OuterIterator;
use Windwalker\Utilities\Test\Iterator\NestedIteratorTest;

/**
 * Rewindable Generator.
 *
 * This is a simple wrapper of generator factory. Generator is un-rewindable, so you can create this
 * Iterator with a generator factory callback. Every time this iterator rewind, the factory callback
 * will execute again to create a new Generator.
 *
 * ```php
 * $iter = new RewindableGenerator(fn () => yield ...);
 * iterator_to_array($iter);
 *
 * $iter->rewind(); // Will re-create a new Generator.
 * ```
 *
 * @test {@see NestedIteratorTest}
 */
class RewindableGenerator implements OuterIterator
{
    /**
     * @var callable
     */
    protected $callable;

    protected ?Generator $generator = null;

    /**
     * RewindableGenerator constructor.
     *
     * @param  callable  $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * getGenerator
     *
     * @param  bool  $refresh
     *
     * @return  Generator
     */
    protected function getGenerator(bool $refresh = false): Generator
    {
        if (!$this->generator || $refresh) {
            $this->generator = ($this->callable)();
        }

        return $this->generator;
    }

    /**
     * @inheritDoc
     */
    public function current(): mixed
    {
        return $this->getGenerator()->current();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->getGenerator()->next();
    }

    /**
     * @inheritDoc
     */
    public function key(): float|bool|int|string|null
    {
        return $this->getGenerator()->key();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->getGenerator()->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->getGenerator(true);
    }

    /**
     * @inheritDoc
     */
    public function getInnerIterator(): Iterator|Generator
    {
        return $this->getGenerator();
    }
}
