<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Generator;
use IteratorAggregate;
use Psr\EventDispatcher\EventDispatcherInterface;
use Windwalker\Data\Collection;
use Windwalker\Event\EventListenableInterface;
use Windwalker\Query\Bounded\BindableInterface;

/**
 * Interface StatementInterface
 */
interface StatementInterface extends BindableInterface, IteratorAggregate, EventListenableInterface
{
    /**
     * execute
     *
     * @param  array|null  $params
     *
     * @return  static
     */
    public function execute(?array $params = null): static;

    /**
     * Fetch 1 row and move cursor to next position.
     *
     * @param  string|object|null  $class
     * @param  array               $args
     *
     * @return  Collection|null
     */
    public function fetch(string|object|null $class = null, array $args = []): ?object;

    /**
     * Fetch 1 row and close ths cursor.
     *
     * @template T
     *
     * @param  string|object|T  $class
     * @param  array            $args
     *
     * @return  Collection|T|null
     */
    public function get(string|object|null $class = null, array $args = []): ?object;

    /**
     * Fetch all items and close cursor.
     *
     * @template T
     *
     * @param  string|object|T  $class
     * @param  array            $args
     *
     * @return Collection|Collection[]|T[]
     */
    public function all(string|object|null $class = null, array $args = []): Collection;

    /**
     * Fetch all column values and close the cursor.
     *
     * @param  int|string  $offset
     *
     * @return  Collection
     */
    public function loadColumn(int|string $offset = 0): Collection;

    /**
     * Fetch first cell and close the cursor.
     *
     * @param  bool  $throwsIfNotFound  Throws Exception if not found.
     *
     * @return  mixed
     */
    public function result(bool $throwsIfNotFound = false): mixed;

    /**
     * Close cursor and free result.
     *
     * @return  static
     */
    public function close(): static;

    /**
     * Count results.
     *
     * @return  int
     */
    public function countAffected(): int;

    /**
     * Get current cursor.
     *
     * @return  mixed
     */
    public function getCursor(): mixed;

    /**
     * isExecuted
     *
     * @return  bool
     */
    public function isExecuted(): bool;

    /**
     * getIterator
     *
     * @param  string|object|null  $class
     * @param  array               $args
     *
     * @return  \Traversable
     */
    public function getIterator(string|object|null $class = null, array $args = []): \Traversable;

    /**
     * addDispatcherDealer
     *
     * @param  EventDispatcherInterface  $dispatcher
     *
     * @return  void
     */
    public function addDispatcherDealer(EventDispatcherInterface $dispatcher): void;

    /**
     * Method to get last auto-increment ID value.
     *
     * @param  string|null  $sequence
     *
     * @return string|null
     */
    public function lastInsertId(?string $sequence = null): ?string;

    /**
     * @return string
     */
    public function getQuery(): string;

    /**
     * @param  mixed  $key
     *
     * @return  array|null
     */
    public function &getBounded(mixed $key = null): ?array;

    /**
     * @return string
     */
    public function getDefaultItemClass(): string;

    /**
     * @param  string  $defaultItemClass
     *
     * @return  static  Return self to support chaining.
     */
    public function setDefaultItemClass(string $defaultItemClass): static;
}
