<?php

declare(strict_types=1);

namespace Windwalker\Utilities;

use Traversable;
use Windwalker\Utilities\Wrapper\WrapperInterface;

class Piping implements \JsonSerializable, \Countable, \IteratorAggregate, \Stringable, WrapperInterface
{
    public function __construct(public mixed $value)
    {
    }

    public function pipe(callable $callback, ...$args): static
    {
        $this->value = $callback($this->value, ...$args);

        return $this;
    }

    public function tap(callable $callback, ...$args): static
    {
        $callback($this->value, ...$args);

        return $this;
    }

    public function getIterator(): Traversable
    {
        return $this->value;
    }

    public function count(): int
    {
        return is_countable($this->value) ? count($this->value) : 0;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    public function __invoke(mixed $src = null): mixed
    {
        return $this->value;
    }
}
