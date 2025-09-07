<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Generator;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Throwable;
use Windwalker\Data\Collection;
use Windwalker\Database\Event\HydrateEvent;
use Windwalker\Database\Event\ItemFetchedEvent;
use Windwalker\Database\Event\QueryEndEvent;
use Windwalker\Database\Event\QueryFailedEvent;
use Windwalker\Database\Event\QueryStartEvent;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Query\Bounded\BindableTrait;

use function Windwalker\collect;

/**
 * The AbstractStatement class.
 */
abstract class AbstractStatement implements StatementInterface
{
    use BindableTrait;
    use EventAwareTrait;

    /**
     * @var mixed|resource
     */
    public protected(set) mixed $cursor = null;

    protected mixed $conn = null;

    /**
     * @var bool
     */
    public protected(set) bool $executed = false;

    /**
     * @var AbstractDriver
     */
    protected AbstractDriver $driver;

    /**
     * @var string
     */
    public protected(set) string $query;

    protected array $options = [];

    public protected(set) string $defaultItemClass = Collection::class;

    /**
     * AbstractStatement constructor.
     *
     * @param  AbstractDriver  $driver
     * @param  string          $query
     * @param  array           $bounded
     * @param  array           $options
     */
    public function __construct(AbstractDriver $driver, string $query, array $bounded = [], array $options = [])
    {
        $this->driver = $driver;
        $this->query = $query;
        $this->bounded = $bounded;
        $this->options = $options;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function getIterator(string|object|null $class = null, array $args = []): Generator
    {
        $this->execute();

        while (($row = $this->fetch($class, $args)) !== null) {
            yield $row;
        }
    }

    /**
     * @inheritDoc
     */
    public function fetch(string|object|null $class = null, array $args = []): ?object
    {
        // todo: Implement more hydrators strategies.
        $hydrator = $this->driver->getHydrator();

        $item = $this->doFetch();

        if (is_object($class)) {
            $class = $class::class;
        }

        $class ??= $this->getDefaultItemClass() ?: Collection::class;

        $item = $this->fetchedEvent($item);

        $item = $this->emit(
            new HydrateEvent(
                item: $item,
                class: $class,
                sql: $this->query,
                statement: $this
            ),
        )->item;

        if (!is_array($item)) {
            return $item;
        }

        return $hydrator->hydrate(
            $item,
            is_string($class) ? new $class() : $class
        );
    }

    abstract protected function doFetch(array $args = []): ?array;

    /**
     * @param  array|null  $params
     *
     * @return  static
     * @throws Throwable
     */
    public function execute(?array $params = null): static
    {
        if ($this->executed) {
            return $this;
        }

        $this->emit(new QueryStartEvent(sql: $this->query, statement: $this, params: $params));

        try {
            $result = $this->doExecute($params);

            if (!$result) {
                throw new StatementException('Execute query statement failed.');
            }
        } catch (RuntimeException $exception) {
            $this->close();

            throw $this->emit(new QueryFailedEvent(exception: $exception))->exception;
        }

        $this->emit(new QueryEndEvent(result: $result, sql: $this->query, statement: $this));

        $this->executed = true;

        return $this;
    }

    /**
     * Execute query by driver.
     *
     * @param  array|null  $params
     *
     * @return  bool
     */
    abstract protected function doExecute(?array $params = null): bool;

    protected function useConnection(\Closure $handler): mixed
    {
        return $this->driver->useConnection($handler);
    }

    /**
     * @inheritDoc
     */
    public function get(string|object|null $class = null, array $args = []): ?object
    {
        $result = $this->fetch($class, $args);

        $this->close();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function all(string|object|null $class = null, array $args = []): Collection
    {
        $this->execute();

        $array = [];

        // Get all of the rows from the result set.
        while ($row = $this->fetch($class, $args)) {
            $array[] = $row;
        }

        $items = collect($array);

        $this->close();

        return $items;
    }

    /**
     * fetchedEvent
     *
     * @param  array|null  $item
     *
     * @return array|null
     */
    protected function fetchedEvent(?array $item): ?array
    {
        $event = $this->emit(
            new ItemFetchedEvent(item: $item, sql: $this->query, statement: $this)
        );

        return $event->item;
    }

    /**
     * @inheritDoc
     */
    public function loadColumn(int|string $offset = 0): Collection
    {
        $all = $this->all(Collection::class);

        if (is_numeric($offset)) {
            return $all->mapProxy()
                ->values()
                ->column($offset);
        }

        return $all->column($offset);
    }

    /**
     * @param  bool  $throwsIfNotFound
     *
     * @inheritDoc
     */
    public function result(bool $throwsIfNotFound = false): mixed
    {
        $assoc = $this->get();

        if ($assoc === null) {
            if ($throwsIfNotFound) {
                throw new StatementException('Query not found', 404);
            }

            return null;
        }

        return $assoc->first();
    }

    /**
     * getInnerStatement
     *
     * @return  mixed|resource
     */
    public function getCursor(): mixed
    {
        return $this->cursor;
    }

    /**
     * isExecuted
     *
     * @return  bool
     */
    public function isExecuted(): bool
    {
        return $this->executed;
    }

    /**
     * @inheritDoc
     */
    public function addDispatcherDealer(EventDispatcherInterface $dispatcher): void
    {
        $this->getEventDispatcher()->addDealer($dispatcher);
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     */
    public function getDefaultItemClass(): string
    {
        return $this->defaultItemClass;
    }

    /**
     * @param  string  $defaultItemClass
     *
     * @return  static  Return self to support chaining.
     */
    public function setDefaultItemClass(string $defaultItemClass): static
    {
        $this->defaultItemClass = $defaultItemClass;

        return $this;
    }
}
