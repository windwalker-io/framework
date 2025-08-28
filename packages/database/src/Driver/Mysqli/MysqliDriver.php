<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Mysqli;

use mysqli;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Driver\TransactionDriverInterface;

/**
 * The MysqliDriver class.
 */
class MysqliDriver extends AbstractDriver implements TransactionDriverInterface
{
    protected static string $name = 'mysqli';

    /**
     * @var string
     */
    protected string $platformName = 'mysql';

    /**
     * @var ?ConnectionInterface
     */
    protected ?ConnectionInterface $connection = null;

    /**
     * @inheritDoc
     */
    public function createStatement(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        return new MysqliStatement($this, $query, $bounded, $options);
    }

    /**
     * @inheritDoc
     */
    public function quote(string $value): string
    {
        return "'" . $this->escape($value) . "'";
    }

    /**
     * @inheritDoc
     */
    public function escape(string $value): string
    {
        return $this->useConnection(
            function (ConnectionInterface $conn) use ($value) {
                /** @var mysqli $mysqli */
                $mysqli = $conn->get();

                return $mysqli->real_escape_string($value);
            }
        );
    }

    /**
     * @param  bool  $keep  *
     *
     * @inheritDoc
     */
    public function getConnection(bool $keep = false): ConnectionInterface
    {
        if ($this->connection) {
            return $this->connection;
        }

        return parent::getConnection($keep);
    }

    /**
     * @inheritDoc
     */
    public function transactionStart(): bool
    {
        /** @var mysqli $mysqli */
        $mysqli = $this->getConnection(true)->get();

        return $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
    }

    /**
     * @param  bool  $releaseConnection  *
     *
     * @inheritDoc
     */
    public function transactionCommit(bool $releaseConnection = true): bool
    {
        /** @var mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        $r = $mysqli->commit();

        if ($releaseConnection) {
            $this->releaseKeptConnection();
        }

        return $r;
    }

    /**
     * @param  bool  $releaseConnection  *
     *
     * @return bool
     * @throws \Throwable
     * @inheritDoc
     */
    public function transactionRollback(bool $releaseConnection = true): bool
    {
        /** @var mysqli $mysqli */
        $mysqli = $this->getConnection()->get();

        $r = $mysqli->rollback();

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
        return $this->useConnection(fn(ConnectionInterface $conn) => $conn->get()->server_version);
    }
}
