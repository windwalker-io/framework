<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use JetBrains\PhpStorm\Pure;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Event\HydrateEvent;
use Windwalker\Database\Event\ItemFetchedEvent;
use Windwalker\Database\Event\QueryEndEvent;
use Windwalker\Database\Event\QueryFailedEvent;
use Windwalker\Database\Event\QueryStartEvent;
use Windwalker\Database\Exception\DatabaseQueryException;
use Windwalker\Database\Hydrator\HydratorAwareInterface;
use Windwalker\Database\Hydrator\HydratorInterface;
use Windwalker\Database\Hydrator\SimpleHydrator;
use Windwalker\Database\Schema\AbstractSchemaManager;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Pool\ConnectionPool;
use Windwalker\Pool\PoolInterface;
use Windwalker\Query\Query;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Options\OptionsResolverTrait;
use Windwalker\Utilities\Options\RecordOptions;
use Windwalker\Utilities\StrNormalize;

/**
 * The AbstractDriver class.
 */
abstract class AbstractDriver implements HydratorAwareInterface
{
    /**
     * @var string
     */
    protected static string $name = '';

    /**
     * @var string
     */
    protected string $platformName = '';

    /**
     * @var Query|string
     */
    protected mixed $lastQuery = null;

    /**
     * @var ?ConnectionInterface
     */
    protected ?ConnectionInterface $connection = null;

    /**
     * @var ?AbstractSchemaManager
     */
    protected ?AbstractSchemaManager $schema = null;

    protected ?PoolInterface $pool = null;

    protected ?HydratorInterface $hydrator = null;

    public protected(set) DriverOptions $options;

    /**
     * AbstractPlatform constructor.
     *
     * @param  array|DriverOptions  $options
     * @param  PoolInterface|null   $pool
     */
    public function __construct(array|DriverOptions $options, ?PoolInterface $pool = null)
    {
        $this->options = clone DriverOptions::wrap($options);

        $this->configureOptions($this->options);

        $this->setPool($pool);

        if ($this->options->platform) {
            $this->setPlatformName($this->options->platform);
        }
    }

    /**
     * @return string|Query
     */
    public function getLastQuery(): mixed
    {
        return $this->lastQuery;
    }

    protected function configureOptions(DriverOptions $options): void
    {
        // $options->host ??= 'localhost';

        TypeAssert::assert($options->driver, 'Driver should not be empty.');
        // TypeAssert::assert($options->host, 'Host should not be empty.');
        // TypeAssert::assert($options->user, 'User should not be empty.');
    }

    protected function handleQuery($query, ?array &$bounded = [], $emulated = false): string
    {
        $this->lastQuery = $query;

        if ($query instanceof Query) {
            return $this->replacePrefix($query->render($emulated, $bounded));
        }

        $bounded = $bounded ?? [];

        return $this->replacePrefix((string) $query);
    }

    /**
     * Get a connection, must release manually.
     *
     * @param  bool  $keep  Keep connection for reuse.
     *
     * @return  ConnectionInterface
     * @throws \Throwable
     */
    public function getConnection(bool $keep = false): ConnectionInterface
    {
        if ($this->connection) {
            return $this->connection;
        }

        $conn = $this->getConnectionFromPool();

        if ($keep) {
            $this->connection = $conn;
        }

        if ($conn->isConnected()) {
            return $conn;
        }

        try {
            $conn->connect();

            $this->runAfterConnects($conn);
        } catch (\Throwable $e) {
            $this->releaseConnection($conn);
            throw $e;
        }

        return $conn;
    }

    protected function runAfterConnects(ConnectionInterface $connection): void
    {
        $driver = clone $this;
        $driver->connection = $connection;

        $afterConnectCallbacks = $this->options->afterConnect;

        if (!is_array($afterConnectCallbacks)) {
            $afterConnectCallbacks = [$afterConnectCallbacks];
        }

        foreach ($afterConnectCallbacks as $callback) {
            if (is_string($callback)) {
                $driver->execute($callback);
            } else {
                $callback($driver);
            }
        }
    }

    public function dropConnection(?ConnectionInterface $conn = null): static
    {
        if ($conn) {
            $conn->release();

            $this->getPool()->dropConnection($conn);
        } elseif ($this->connection) {
            $this->getPool()->dropConnection(
                $this->releaseKeptConnection()
            );
        }

        return $this;
    }

    public function releaseKeptConnection(): ?ConnectionInterface
    {
        if ($this->connection) {
            $conn = $this->connection;

            $conn->release();

            $this->connection = null;

            return $conn;
        }

        return null;
    }

    public function getKeptConnection(): ?ConnectionInterface
    {
        return $this->connection;
    }

    public function releaseConnection(ConnectionInterface $conn): void
    {
        if ($this->connection === $conn) {
            $this->connection = null;
        }

        $conn->release();
    }

    public function useConnection(callable $callback): mixed
    {
        $conn = $this->getConnection();

        try {
            $result = $callback($conn);
        } finally {
            if (!$this->connection) {
                $conn->release();
            }
        }

        return $result;
    }

    public function disconnectAll(): int
    {
        return $this->getPool()->close();
    }

    public function disconnect(): static
    {
        $this->dropConnection();

        return $this;
    }

    /**
     * createStatement
     *
     * @param  string  $query
     * @param  array   $bounded
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    abstract protected function createStatement(
        string $query,
        array $bounded = [],
        array $options = []
    ): StatementInterface;

    /**
     * @inheritDoc
     */
    public function prepare(mixed $query, array $options = []): StatementInterface
    {
        // Convert query to string and get merged bounded
        $sql = $this->handleQuery($query, $bounded);

        // Prepare actions by driver
        $stmt = $this->createStatement($sql, $bounded, $options);

        if ($query instanceof EventAwareInterface) {
            $stmt->addDispatcherDealer($query->getEventDispatcher());
        } elseif ($query instanceof EventDispatcherInterface) {
            $stmt->addDispatcherDealer($query);
        }

        // Register monitor events
        $stmt->on(
            QueryStartEvent::class,
            fn(QueryStartEvent $event) => $event->fill(
                query: $query,
                bounded: $bounded,
            )
        );

        $stmt->on(
            QueryFailedEvent::class,
            function (QueryFailedEvent $event) use ($query, $bounded) {
                $event->fill(
                    query: $query,
                    bounded: $bounded,
                );

                $e = $event->exception;

                $debugSql = $this->replacePrefix(($query instanceof Query ? $query->render(true) : (string) $query));

                $message = $e->getMessage();

                $message .= ' - SQL: ' . $debugSql;

                $event->exception = new DatabaseQueryException(
                    $message,
                    (int) $e->getCode(),
                    $e
                )->setDebugSql($debugSql);
            }
        );

        $stmt->on(
            QueryEndEvent::class,
            fn(QueryEndEvent $event) => $event->fill(
                query: $query,
                bounded: $bounded,
            )
        );

        $stmt->on(
            HydrateEvent::class,
            fn(HydrateEvent $event) => $event->fill(
                query: $query,
                bounded: $bounded,
            )
        );

        $stmt->on(
            ItemFetchedEvent::class,
            fn(ItemFetchedEvent $event) => $event->fill(
                query: $query,
                bounded: $bounded,
            )
        );

        return $stmt;
    }

    /**
     * @inheritDoc
     */
    public function execute(mixed $query, ?array $params = null): StatementInterface
    {
        return $this->prepare($query)->execute($params);
    }

    abstract public function getVersion(): string;

    /**
     * Replace the table prefix.
     *
     * @see     https://stackoverflow.com/a/31745275
     *
     * @param  string  $sql     The SQL statement to prepare.
     * @param  string  $prefix  The common table prefix.
     *
     * @return  string  The processed SQL statement.
     */
    public function replacePrefix(string $sql, string $prefix = '#__'): string
    {
        if ($prefix === '' || !str_contains($sql, $prefix)) {
            return $sql;
        }

        $array = [];

        if ($number = preg_match_all('#((?<![\\\])[\'"])((?:.(?!(?<![\\\])\1))*.?)\1#i', $sql, $matches)) {
            for ($i = 0; $i < $number; $i++) {
                if (!empty($matches[0][$i])) {
                    $array[$i] = trim($matches[0][$i]);
                    $sql = str_replace($matches[0][$i], '<#encode:' . $i . ':code#>', $sql);
                }
            }
        }

        $sql = str_replace($prefix, $this->getOption('prefix'), $sql);

        foreach ($array as $key => $js) {
            $sql = str_replace('<#encode:' . $key . ':code#>', $js, $sql);
        }

        return $sql;
    }

    /**
     * @return string
     */
    public function getPlatformName(): string
    {
        return $this->platformName;
    }

    /**
     * @param  string  $platformName
     *
     * @return  static  Return self to support chaining.
     */
    public function setPlatformName(string $platformName): static
    {
        $this->platformName = $platformName;

        return $this;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnectionFromPool(): ConnectionInterface
    {
        $pool = $this->getPool();
        $pool->init();

        /** @var ConnectionInterface $connection */
        $connection = $pool->getConnection();

        return $connection;
    }

    public function createConnection(): ConnectionInterface
    {
        $class = $this->getConnectionClass();

        return new $class($this->getOptions());
    }

    #[Pure]
    protected function getConnectionClass(): string
    {
        $class = __NAMESPACE__ . '\%s\%sConnection';

        return sprintf(
            $class,
            ucfirst(static::$name),
            ucfirst(static::$name)
        );
    }

    public function isSupported(): bool
    {
        return $this->getConnectionClass()::isSupported();
    }

    public function __destruct()
    {
        $this->disconnectAll();
    }

    /**
     * @param  PoolInterface|null  $pool
     *
     * @return  static  Return self to support chaining.
     */
    public function setPool(?PoolInterface $pool): static
    {
        $this->pool = $pool;

        if ($this->pool) {
            $this->preparePoolConnectionBuilder($this->pool);
        }

        return $this;
    }

    public function preparePoolConnectionBuilder(PoolInterface $pool): PoolInterface
    {
        $pool->setConnectionBuilder(
            fn() => $this->createConnection()
        );

        return $pool;
    }

    protected function createDefaultPool(): ConnectionPool
    {
        $options = $this->getOptions();

        $pool = (new DatabaseFactory())
            ->createConnectionPool($options['pool'] ?? []);

        $this->preparePoolConnectionBuilder($pool);

        return $pool;
    }

    /**
     * @return PoolInterface
     */
    public function getPool(): PoolInterface
    {
        return $this->pool ??= $this->createDefaultPool();
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator ??= new SimpleHydrator();
    }

    /**
     * @param  HydratorInterface|null  $hydrator
     *
     * @return  static  Return self to support chaining.
     */
    public function setHydrator(?HydratorInterface $hydrator): static
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    public function isDebug(): bool
    {
        return (bool) $this->getOption('debug');
    }

    /**
     * Quote and escape a value.
     *
     * @param  string  $value
     *
     * @return  string
     */
    abstract public function quote(string $value): string;

    /**
     * Escape a value.
     *
     * @param  string  $value
     *
     * @return  string
     */
    abstract public function escape(string $value): string;

    public function getOptions(): RecordOptions
    {
        return $this->options;
    }

    public function getOption(string $name): mixed
    {
        $name = StrNormalize::toCamelCase($name);

        return $this->options->$name ?? null;
    }
}
