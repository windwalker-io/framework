<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

use Traversable;

final class WeakObjectStorage implements \IteratorAggregate, \Countable
{
    protected static array $instances = [];

    protected \WeakMap $map;

    public function __construct()
    {
        $this->map = new \WeakMap();
    }

    public static function getInstance(string $name = 'main'): self
    {
        return self::$instances[$name] ??= new self();
    }

    public static function removeInstance(string $name = 'main'): void
    {
        unset(self::$instances[$name]);
    }

    public function get(object $item): mixed
    {
        return $this->getMap()[$item] ?? null;
    }

    public function set(object $item, mixed $value): static
    {
        $this->getMap()[$item] = $value;

        return $this;
    }

    public function remove(object $item): void
    {
        unset($this->getMap()[$item]);
    }

    public function has(object $item): bool
    {
        return isset($this->getMap()[$item]);
    }

    public function getMap(): \WeakMap
    {
        return $this->map;
    }

    public function count(): int
    {
        return count($this->getMap());
    }

    public function getIterator(): Traversable
    {
        foreach ($this->getMap() as $k => $v) {
            yield $k => $v;
        }
    }
}
