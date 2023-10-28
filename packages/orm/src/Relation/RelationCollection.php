<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

use ArrayAccess;
use Exception;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use Windwalker\Data\Collection;
use Windwalker\ORM\SelectorQuery;
use Windwalker\Utilities\TypeCast;

/**
 * The RelationCollections class.
 */
class RelationCollection implements IteratorAggregate, JsonSerializable, ArrayAccess, \Countable
{
    /**
     * @var object[]
     */
    protected array $attachedEntities = [];

    /**
     * @var object[]
     */
    protected array $detachedEntities = [];

    /**
     * @var object[]
     */
    public ?array $cache = null;

    protected bool $sync = false;

    /**
     * RelationCollection constructor.
     *
     * @param  string              $className
     * @param  SelectorQuery|null  $query
     */
    public function __construct(
        protected string $className,
        protected ?SelectorQuery $query = null
    ) {
        //
    }

    public function attach(object|array $entities): static
    {
        if (is_object($entities)) {
            $entities = [$entities];
        }

        foreach ($entities as $entity) {
            $hash = spl_object_hash($entity);

            unset($this->detachedEntities[$hash]);

            $this->attachedEntities[$hash] = $entity;
        }

        return $this;
    }

    public function cancelAttach(object $entity): static
    {
        unset($this->attachedEntities[spl_object_hash($entity)]);

        return $this;
    }

    public function detach(object|array $entities): static
    {
        if (is_object($entities)) {
            $entities = [$entities];
        }

        foreach ($entities as $entity) {
            $hash = spl_object_hash($entity);

            unset($this->attachedEntities[$hash]);

            $this->detachedEntities[$hash] = $entity;
        }

        return $this;
    }

    public function cancelDetach(object $entity): static
    {
        unset($this->detachedEntities[spl_object_hash($entity)]);

        return $this;
    }

    public function clearAttachesAndDetaches(): static
    {
        $this->attachedEntities = [];
        $this->detachedEntities = [];

        return $this;
    }

    public function clearCache(): static
    {
        $this->cache = null;
        $this->sync = false;

        return $this;
    }

    public function clearAll(): static
    {
        return $this->clearAttachesAndDetaches()->clearCache();
    }

    /**
     * all
     *
     * @param  string|null  $class
     *
     * @return Collection
     * @throws Exception
     */
    public function all(?string $class = null): Collection
    {
        return \Windwalker\collect(iterator_to_array($this->getIterator($class)));
    }

    /**
     * loadColumn
     *
     * @param  int|string  $offset
     *
     * @return  Collection
     */
    public function loadColumn(int|string $offset = 0): Collection
    {
        if ($this->query === null) {
            return \Windwalker\collect();
        }

        return $this->query->loadColumn($offset);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(?string $class = null): Generator
    {
        if ($this->query === null) {
            return;
        }

        $iterator = $this->cache ?? $this->query->getIterator($class ?? $this->className);

        $cache = [];

        foreach ($iterator as $k => $item) {
            $cache[$k] = $item;

            yield $k => $item;
        }

        $this->cache = $cache;
    }

    /**
     * @return SelectorQuery|null
     */
    public function getQuery(): ?SelectorQuery
    {
        return clone $this->query;
    }

    /**
     * @return object[]
     */
    public function getAttachedEntities(): array
    {
        return $this->attachedEntities;
    }

    /**
     * @param  object[]  $attachedEntities
     *
     * @return  static  Return self to support chaining.
     */
    public function setAttachedEntities(array $attachedEntities): static
    {
        $this->attachedEntities = $attachedEntities;

        return $this;
    }

    /**
     * @return object[]
     */
    public function getDetachedEntities(): array
    {
        return $this->detachedEntities;
    }

    /**
     * @param  object[]  $detachedEntities
     *
     * @return  static  Return self to support chaining.
     */
    public function setDetachedEntities(array $detachedEntities): static
    {
        $this->detachedEntities = $detachedEntities;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->cache ?? [];
    }

    /**
     * @return bool
     */
    public function isSync(): bool
    {
        return $this->sync;
    }

    /**
     * @param  array  $items
     *
     * @return  static  Return self to support chaining.
     */
    public function sync(iterable $items): static
    {
        $this->sync = true;

        $this->cache = TypeCast::toArray($items);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        $this->all();

        return isset($this->cache[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): mixed
    {
        $this->all();

        return $this->cache[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->all();

        $this->cache[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->all();

        unset($this->cache[$offset]);
    }

    public function count(): int
    {
        return $this->getQuery()?->count() ?? 0;
    }
}
