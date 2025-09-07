<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use PDO;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Driver\TransactionDriverInterface;
use Windwalker\Query\Escaper;

/**
 * The PdoDriver class.
 */
class PdoDriver extends AbstractDriver implements TransactionDriverInterface
{
    /**
     * @var string
     */
    protected static string $name = 'pdo';

    /**
     * @var string
     */
    public protected(set) string $platformName = 'odbc';

    protected function getConnectionClass(): string
    {
        $platformName = $this->options->platform ?? $this->options->driver;

        return sprintf(
            __NAMESPACE__ . '\Pdo%sConnection',
            ucfirst(DatabaseFactory::getDriverShortName($platformName))
        );
    }

    /**
     * doPrepare
     *
     * @param  string  $query
     * @param  array   $bounded
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    public function createStatement(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        return new PdoStatement($this, $query, $bounded, $options);
    }

    /**
     * @inheritDoc
     */
    public function quote(string $value): string
    {
        return $this->useConnection(
            function (ConnectionInterface $conn) use ($value) {
                /** @var PDO $pdo */
                $pdo = $conn->get();

                return $pdo->quote($value);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function escape(string $value): string
    {
        return Escaper::stripQuote($this->quote($value));
    }

    /**
     * @inheritDoc
     */
    public function transactionStart(): bool
    {
        $connection = $this->getConnection(true);

        /** @var PDO $pdo */
        $pdo = $connection->get();

        return $pdo->beginTransaction();
    }

    /**
     * @param  bool  $releaseConnection  *
     * @inheritDoc
     */
    public function transactionCommit(bool $releaseConnection = true): bool
    {
        /** @var PDO $pdo */
        $pdo = $this->getConnection()->get();

        $r = $pdo->commit();

        if ($releaseConnection) {
            $this->releaseKeptConnection();
        }

        return $r;
    }

    /**
     * @param  bool  $releaseConnection  *
     * @inheritDoc
     */
    public function transactionRollback(bool $releaseConnection = true): bool
    {
        /** @var PDO $pdo */
        $pdo = $this->getConnection()->get();

        $r = $pdo->rollBack();

        if ($releaseConnection) {
            $this->releaseKeptConnection();
        }

        return $r;
    }

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string
    {
        return $this->useConnection(
            fn(ConnectionInterface $conn) => $conn->get()->getAttribute(PDO::ATTR_SERVER_VERSION)
        );
    }
}
