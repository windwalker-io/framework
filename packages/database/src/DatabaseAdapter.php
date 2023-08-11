<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database;

use BadMethodCallException;
use DateTime;
use DateTimeInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Stringable;
use Throwable;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Hydrator\HydratorAwareInterface;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\Database\Manager\DatabaseManager;
use Windwalker\Database\Manager\SchemaManager;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Manager\WriterManager;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Event\EventListenableInterface;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\ORM;
use Windwalker\Query\Query;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

use function Windwalker\raw;

/**
 * The DatabaseAdapter class.
 *
 * @method Query select(...$columns)
 * @method Query update(string $table, ?string $alias = null)
 * @method Query delete(string $table, ?string $alias = null)
 * @method Query insert(string $table, bool $incrementField = false)
 */
class DatabaseAdapter implements EventAwareInterface, HydratorAwareInterface
{
    use EventAwareTrait;
    use InstanceCacheTrait;

    /**
     * @var Query|string|Stringable|null
     */
    protected mixed $query = null;

    protected ?ORM $orm = null;

    /**
     * DatabaseAdapter constructor.
     *
     * @param  AbstractDriver   $driver
     * @param  AbstractPlatform $platform
     * @param  LoggerInterface  $logger
     */
    public function __construct(
        protected AbstractDriver $driver,
        protected AbstractPlatform $platform,
        protected LoggerInterface $logger = new NullLogger(),
    ) {
        $this->platform->setDbAdapter($this);
    }

    /**
     * Force close all connections from pool.
     *
     * @return  int
     */
    public function disconnect(): int
    {
        return $this->getDriver()->disconnectAll();
    }

    public function prepare(mixed $query, array $options = []): StatementInterface
    {
        $this->query = $query;

        $stmt = $this->getDriver()->prepare($query, $options);

        // Make DatabaseAdapter listen to statement events
        $stmt->addDispatcherDealer($this->getEventDispatcher());

        return $stmt;
    }

    public function execute(mixed $query, ?array $params = null): StatementInterface
    {
        $this->query = $query;

        return $this->prepare($query)->execute($params);
    }

    /**
     * getQuery
     *
     * @param  bool  $new
     *
     * @return string|Query
     */
    public function getQuery(bool $new = false): Query|Stringable|string|null
    {
        if ($new) {
            return $this->getPlatform()->createQuery($this);
        }

        return $this->query;
    }

    public function createQuery(): Query
    {
        return $this->getQuery(true);
    }

    /**
     * To support escape() and quote() methods, we must cache a Query object and reuse it.
     *
     * This Query must set Driver as escaper to prevent infinity-loop.
     *
     * @param  bool  $new
     *
     * @return  Query
     */
    protected function getEscaperQuery(bool $new = false): Query
    {
        return $this->once(
            'cached.query',
            fn() => $this->getPlatform()
                ->createQuery($this->getDriver()),
            $new
        );
    }

    /**
     * quoteName
     *
     * @param  mixed  $value
     * @param  bool   $ignoreDot
     *
     * @return  array|string
     */
    public function quoteName(string|Stringable $value, bool $ignoreDot = false): array|string
    {
        return $this->getPlatform()->getGrammar()::quoteName($value, $ignoreDot);
    }

    public function quote(mixed $value): array|string
    {
        return $this->getEscaperQuery()->quote($value);
    }

    public function escape(mixed $value): array|string
    {
        return $this->getEscaperQuery()->escape($value);
    }

    /**
     * listDatabases
     *
     * @return  array
     */
    public function listDatabases(): array
    {
        return $this->getPlatform()->listDatabases();
    }

    /**
     * listDatabases
     *
     * @return  array
     */
    public function listSchemas(): array
    {
        return $this->getPlatform()->listSchemas();
    }

    /**
     * listTables
     *
     * @param  string|null  $schema
     * @param  bool         $includeViews
     *
     * @return  array
     */
    public function listTables(?string $schema = null, bool $includeViews = false): array
    {
        return $this->getSchema($schema)->getTables($includeViews);
    }

    public function getOptions(): array
    {
        return $this->getDriver()->getOptions();
    }

    public function getDriverName(): string
    {
        return $this->getDriver()->getOption('driver');
    }

    /**
     * @return AbstractDriver
     */
    public function getDriver(): AbstractDriver
    {
        return $this->driver;
    }

    /**
     * @return AbstractPlatform
     */
    public function getPlatform(): AbstractPlatform
    {
        return $this->platform;
    }

    public function getDatabase(string $name = null, $new = false): DatabaseManager
    {
        $name = $name ?? $this->getDriver()->getOption('dbname');

        return $this->once('database.' . $name, fn() => new DatabaseManager($name, $this), $new);
    }

    public function getSchema(?string $name = null, $new = false): SchemaManager
    {
        $name = $name ?? $this->getPlatform()::getDefaultSchema();

        return $this->once('schema.' . $name, fn() => new SchemaManager($name, $this), $new);
    }

    public function getTable(string $name, $new = false): TableManager
    {
        return $this->getSchema()->getTable($name, $new);
    }

    public function getWriter($new = false): WriterManager
    {
        return $this->once('writer', fn() => new WriterManager($this), $new);
    }

    public function replacePrefix(string $query, string $prefix = '#__'): string
    {
        return $this->getDriver()->replacePrefix($query, $prefix);
    }

    /**
     * getLastQuery
     *
     * @return  Query|string|null
     */
    public function getLastQuery(): mixed
    {
        return $this->getDriver()->getLastQuery();
    }

    /**
     * transaction
     *
     * @param  callable  $callback
     * @param  bool      $autoCommit
     * @param  bool      $enabled
     *
     * @return  mixed
     *
     * @throws Throwable
     */
    public function transaction(callable $callback, bool $autoCommit = true, bool $enabled = true): mixed
    {
        return $this->getPlatform()->transaction($callback, $autoCommit, $enabled);
    }

    public function countWith(Query|string $query): int
    {
        // Use fast COUNT(*) on Query objects if there no GROUP BY or HAVING clause:
        if (
            $query instanceof Query
            && $query->getType() === Query::TYPE_SELECT
            && $query->getGroup() === null
            && $query->getHaving() === null
        ) {
            $query = clone $query;

            $query->clear('select', 'order', 'limit')->selectRaw('COUNT(*)');

            return (int) $query->result();
        }

        // Otherwise fall back to inefficient way of counting all results.
        if ($query instanceof Query) {
            $subQuery = clone $query;

            $subQuery->clear('select', 'order', 'limit')
                ->selectAs(raw('COUNT(*)'), 'count');
        } else {
            $subQuery = $query->getSql();
        }

        $query = $this->createQuery();

        $query->selectRaw('COUNT(%n)', 'count')
            ->from($subQuery, 'c');

        return (int) $this->prepare($query)->result();
    }

    /**
     * getDateFormat
     *
     * @return  string
     */
    public function getDateFormat(): string
    {
        return $this->getPlatform()->getGrammar()::dateFormat();
    }

    /**
     * getNullDate
     *
     * @return  string
     */
    public function getNullDate(): string
    {
        return $this->getPlatform()->getGrammar()::nullDate();
    }

    public function isNullDate(string|int|null|DateTimeInterface $date): bool
    {
        if ($date === null || $date === '') {
            return true;
        }

        if (is_numeric($date)) {
            $date = new DateTime($date);
        }

        if ($date instanceof DateTimeInterface) {
            $date = $date->format($this->getDateFormat());
        }

        return $date === $this->getNullDate() || $date === '0000-00-00 00:00:00';
    }

    public function __call(string $name, array $args)
    {
        $queryMethods = [
            'select',
            'delete',
            'update',
            'insert',
        ];

        if (in_array(strtolower($name), $queryMethods, true)) {
            return $this->createQuery()->$name(...$args);
        }

        throw new BadMethodCallException(
            sprintf('Call to undefined method of: %s::%s()', static::class, $name)
        );
    }

    /**
     * @inheritDoc
     */
    public function getHydrator(): HydratorInterface
    {
        return $this->getDriver()->getHydrator();
    }

    /**
     * @template T
     *
     * @param  string|T  $entityClass
     *
     * @return EntityMapper<T>
     * @throws \ReflectionException
     */
    public function mapper(string $entityClass): EntityMapper
    {
        return $this->orm()->mapper($entityClass);
    }

    /**
     * @return ORM
     */
    public function orm(): ORM
    {
        if (!class_exists(ORM::class)) {
            throw new DomainException('Please install windwalker/orm ^4.0 first.');
        }

        return $this->orm ??= new ORM($this);
    }

    /**
     * @param  ORM|null  $orm
     *
     * @return  static  Return self to support chaining.
     */
    public function setORM(?ORM $orm): static
    {
        $this->orm = $orm;

        return $this;
    }
}
