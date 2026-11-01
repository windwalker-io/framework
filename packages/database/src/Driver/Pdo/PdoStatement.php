<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Query\Bounded\ParamType;

/**
 * The PdoStatement class.
 *
 * @method  \PDOStatement  getCursor()
 */
class PdoStatement extends AbstractStatement
{
    /**
     * @var  \PDOStatement
     */
    public protected(set) mixed $cursor = null;

    /**
     * @var  PDO
     */
    protected mixed $conn = null;

    /**
     * @inheritDoc
     */
    protected function doExecute(?array $params = null): bool
    {
        return $this->useConnection(
            function (ConnectionInterface $conn) use ($params) {
                /** @var PDO $pdo */
                $this->conn = $pdo = $conn->get();

                $this->cursor = $stmt = $pdo->prepare($query = $this->query, $this->options);

                foreach ($this->getBounded() as $key => $bound) {
                    if (is_int($key)) {
                        ++$key;
                    } elseif (!str_contains($query, ':' . ltrim($key, ':'))) {
                        continue;
                    }

                    $stmt->bindParam(
                        $key,
                        $bound['value'],
                        ParamType::convertToPDO($bound['dataType'] ?? null),
                        $bound['length'] ?? 0,
                        $bound['driverOptions'] ?? null
                    );
                }

                return (bool) $this->cursor->execute($params);
            }
        );
    }

    /**
     * @inheritDoc
     */
    protected function doFetch(array $args = []): ?array
    {
        $this->execute();

        $item = $this->cursor->fetch(PDO::FETCH_ASSOC);

        if ($item === false) {
            return null;
        }

        // pdo_oci returns LOB columns as streams.
        foreach ($item as $k => $v) {
            if (is_resource($v)) {
                $item[$k] = stream_get_contents($v);
            }
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function close(): static
    {
        if ($this->cursor) {
            $this->cursor->closeCursor();
        }

        $this->cursor = null;
        $this->executed = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function countAffected(): int
    {
        if (!$this->cursor) {
            throw new StatementException('Cursor not exists or statement closed.');
        }

        return $this->cursor->rowCount();
    }

    /**
     * @inheritDoc
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        if ($this->conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'oci') {
            return $this->lastOracleInsertId($sequence);
        }

        return $this->conn->lastInsertId($sequence);
    }

    /**
     * PDO_OCI does not support lastInsertId(), read the identity sequence of the inserted table instead.
     */
    protected function lastOracleInsertId(?string $sequence = null): ?string
    {
        if ($sequence === null) {
            $identifier = '(?:"[^"]+"|[\w$#]+)';

            if (
                !preg_match(
                    '/^\s*insert\s+(?:all\s+)?into\s+(' . $identifier . ')(?:\.(' . $identifier . '))?/i',
                    $this->query,
                    $matches
                )
            ) {
                return null;
            }

            $normalize = static fn(string $name) => str_starts_with($name, '"')
                ? trim($name, '"')
                : strtoupper($name);

            $owner = isset($matches[2]) ? $normalize($matches[1]) : null;
            $table = $normalize($matches[2] ?? $matches[1]);

            $stmt = $this->conn->prepare(
                'SELECT SEQUENCE_NAME, OWNER FROM ALL_TAB_IDENTITY_COLS WHERE TABLE_NAME = ? '
                . 'AND OWNER = COALESCE(?, SYS_CONTEXT(\'USERENV\', \'CURRENT_SCHEMA\'))'
            );
            $stmt->execute([$table, $owner]);
            $row = $stmt->fetch(PDO::FETCH_NUM);

            if (!$row) {
                return null;
            }

            $sequence = '"' . $row[1] . '"."' . $row[0] . '"';
        }

        $id = $this->conn->query('SELECT ' . $sequence . '.CURRVAL FROM DUAL')->fetchColumn();

        return $id === false ? null : (string) $id;
    }
}
