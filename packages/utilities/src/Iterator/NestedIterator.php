<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use ArrayIterator;
use Generator;
use Iterator;
use OuterIterator;
use Traversable;
use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\Context\Loop;

/**
 * The MultiLevelIterator class.
 */
class NestedIterator implements OuterIterator
{
    protected Traversable $innerIterator;

    protected ?Iterator $compiledIterator = null;

    /**
     * @var callable[]
     */
    protected array $callbacks = [];

    protected static ?Loop $currentLoop = null;

    /**
     * FilesIterator constructor.
     *
     * @param  iterable|callable  $iterator
     */
    public function __construct(iterable|callable $iterator)
    {
        if (is_callable($iterator)) {
            $iterator = new RewindableGenerator($iterator);
        }

        $this->innerIterator = $iterator instanceof Traversable
            ? $iterator
            : new ArrayIterator($iterator);
    }

    /**
     * wrap
     *
     * @param  callable  $callback
     *
     * @return  static
     */
    public function wrap(callable $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * wrap
     *
     * @param  callable  $callback
     *
     * @return  static
     */
    public function with(callable $callback): static
    {
        $new = $this->cloneNew();

        $new->callbacks = $this->callbacks;
        $new->callbacks[] = $callback;

        return $new;
    }

    public function withSelf(callable $callback): self
    {
        $new = new self($this->getInnerIterator());

        $new->callbacks = $this->callbacks;
        $new->callbacks[] = $callback;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getInnerIterator(): Iterator
    {
        return $this->innerIterator;
    }

    /**
     * compileIterator
     *
     * @param  bool  $refresh
     *
     * @return  Traversable
     */
    protected function compileIterator(bool $refresh = false): Traversable
    {
        if ($this->compiledIterator === null || $refresh) {
            if ($this->checkRewindable($this->innerIterator)) {
                $this->innerIterator->rewind();
            }

            $iterator = $this->innerIterator;

            foreach ($this->callbacks as $callback) {
                $iterator = (static function () use ($iterator, $callback) {
                    return $callback($iterator);
                })();
            }

            $this->compiledIterator = $iterator;
        }

        return $this->compiledIterator;
    }

    /**
     * filter
     *
     * @param  callable  $callback
     * @param  int       $flags
     *
     * @return  static
     */
    public function filter(callable $callback, int $flags = 0): static
    {
        return $this->with(
            function (iterable $files) use ($flags, $callback) {
                foreach ($files as $key => $item) {
                    if ($flags === ARRAY_FILTER_USE_BOTH) {
                        $result = $callback($item, $key);
                    } elseif ($flags === ARRAY_FILTER_USE_KEY) {
                        $result = $callback($key);
                    } else {
                        $result = $callback($item);
                    }

                    if ($result) {
                        yield $key => $item;
                    }
                }
            }
        );
    }

    public function each(callable $callback): static
    {
        $i = 0;

        $target = [];

        static::$currentLoop = new Loop(iterator_count($this), $target, static::$currentLoop);

        foreach ($this as $key => $value) {
            $target[$key] = $value;

            $callback($value, $key, static::$currentLoop->loop($i, $key));

            if (static::$currentLoop->isStop()) {
                break;
            }

            $i++;
        }

        static::$currentLoop = static::$currentLoop->parent();

        return $this;
    }

    public function map(callable $callback): static
    {
        return $this->with(
            function (iterable $items) use ($callback) {
                foreach ($items as $key => $item) {
                    yield $key => $callback($item);
                }
            }
        );
    }

    public function flatMap(callable $callback): static
    {
        return $this->with(
            function (iterable $items) use ($callback) {
                foreach ($items as $item) {
                    yield from $callback($item);
                }
            }
        );
    }

    public function mapWithKey(callable $callback): static
    {
        return $this->with(
            function (iterable $items) use ($callback) {
                foreach ($items as $key => $item) {
                    $result = (array) $callback($item, $key);

                    $k = array_key_first($result);
                    $value = $result[$k];

                    yield $k => $value;
                }
            }
        );
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $acc = $initial;
        foreach ($this as $key => $item) {
            $acc = $callback($acc, $item, $key);
        }

        return $acc;
    }

    public function concat(iterable ...$iterables): static
    {
        return $this->with(
            function (iterable $items) use ($iterables) {
                $k = 0;

                foreach ($items as $key => $item) {
                    if (is_numeric($key)) {
                        yield $k++ => $item;
                    } else {
                        yield $key => $item;
                    }
                }

                foreach ($iterables as $iterable) {
                    foreach ($iterable as $key => $item) {
                        if (is_numeric($key)) {
                            yield $k++ => $item;
                        } else {
                            yield $key => $item;
                        }
                    }
                }
            }
        );
    }

    public function slice(int $start, ?int $length = null): static
    {
        return $this->with(
            function (iterable $items) use ($length, $start) {
                if ($start < 0) {
                    throw new \InvalidArgumentException('Start offset must be non-negative');
                }

                if ($length !== null && $length < 0) {
                    throw new \InvalidArgumentException('Length must be non-negative');
                }

                if ($length === 0) {
                    return;
                }

                $i = 0;
                $k = 0;
                foreach ($items as $key => $value) {
                    if ($i++ < $start) {
                        continue;
                    }

                    if (!is_numeric($key)) {
                        yield $key => $value;
                    } else {
                        yield $k++ => $value;
                    }

                    if ($length !== null && $i >= $start + $length) {
                        break;
                    }
                }
            }
        );
    }

    public function keys(): static
    {
        return $this->with(
            function (iterable $items) {
                foreach ($items as $key => $item) {
                    yield $key;
                }
            }
        );
    }

    public function values(): static
    {
        return $this->with(
            function (iterable $items) {
                foreach ($items as $key => $item) {
                    yield $item;
                }
            }
        );
    }

    public function implode(string $separator): StringObject
    {
        $str = '';
        $first = true;

        foreach ($this as $item) {
            if ($first) {
                $str .= $item;
                $first = false;
            } else {
                $str .= $separator . $item;
            }
        }

        return \Windwalker\str($str);
    }

    public static function explode(string $separator, string $str, ?int $limit = null): static
    {
        if ($separator === '') {
            throw new \InvalidArgumentException('Separator must be non-empty string');
        }

        return new static(
            function () use ($limit, $str, $separator) {
                $offset = 0;
                $count = 0;
                while (
                    $offset < \strlen($str)
                    && false !== $nextOffset = strpos($str, $separator, $offset)
                ) {
                    yield \substr($str, $offset, $nextOffset - $offset);
                    $offset = $nextOffset + \strlen($separator);

                    $count++;

                    if ($count + 1 === $limit) {
                        break;
                    }
                }
                yield \substr($str, $offset);
            }
        );
    }

    public function chunk(int $size, bool $preserveKeys = false): self
    {
        return $this->withSelf(
            static function (Iterator $iter) use ($preserveKeys, $size) {
                // @see https://blog.kevingomez.fr/2016/02/26/efficiently-creating-data-chunks-in-php/
                $closure = static function () use ($preserveKeys, $iter, $size) {
                    $count = $size;
                    while ($count-- && $iter->valid()) {
                        if ($preserveKeys) {
                            yield $iter->key() => $iter->current();
                        } else {
                            yield $iter->current();
                        }

                        $iter->next();
                    }
                };

                while ($iter->valid()) {
                    yield $closure();
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function current(): mixed
    {
        return $this->compileIterator()->current();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->compileIterator()->next();
    }

    /**
     * @inheritDoc
     */
    public function key(): float|bool|int|string|null
    {
        return $this->compileIterator()->key();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->compileIterator()->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->compileIterator(true);
    }

    /**
     * This iterator unable to use native clone. We clone it manually.
     *
     * @return  static
     */
    protected function cloneNew(): static
    {
        return new static($this->getInnerIterator());
    }

    /**
     * Method to set property innerIterator
     *
     * @param  Traversable  $innerIterator
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setInnerIterator(Traversable $innerIterator): static
    {
        $this->innerIterator = $innerIterator;

        return $this;
    }

    /**
     * checkRewindable
     *
     * @param  Traversable  $iter
     *
     * @return  bool
     */
    protected function checkRewindable(Traversable $iter): bool
    {
        if ($iter instanceof Generator) {
            return false;
        }

        if ($iter instanceof OuterIterator) {
            if ($iter->getInnerIterator() === null) {
                return true;
            }

            return $this->checkRewindable($iter->getInnerIterator());
        }

        return $iter instanceof Iterator;
    }
}
