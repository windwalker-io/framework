<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pgsql;

use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Bounded\ParamType;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\PostgreSQLGrammar;
use Windwalker\Query\Query;

use function Windwalker\uid;

/**
 * The PgsqlStatement class.
 */
class PgsqlStatement extends AbstractStatement
{
    /**
     * @var mixed|null
     */
    protected mixed $conn = null;

    /**
     * @inheritDoc
     */
    protected function doExecute(?array $params = null): bool
    {
        if ($params !== null) {
            // Convert array to bounded params
            $params = array_map(
                static function ($param) {
                    return [
                        'value' => $param,
                        'dataType' => ParamType::guessType($param),
                    ];
                },
                $params
            );
        } else {
            $params = $this->bounded;
        }

        [$query, $params] = BoundedHelper::replaceParams($this->query, '$%d', $params);

        $this->driver->useConnection(
            function (ConnectionInterface $conn) use ($params, $query) {
                $this->conn = $resource = $conn->get();

                pg_prepare($resource, $stname = uid('pg-'), $query);

                $args = [];

                foreach ($params as $param) {
                    $args[] = &$param['value'];
                }

                $this->cursor = pg_execute($resource, $stname, $args);
            }
        );

        if (!$this->cursor) {
            throw new StatementException(pg_last_error());
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function doFetch(array $args = []): ?array
    {
        $this->execute();

        $row = pg_fetch_assoc($this->cursor);

        return $row ?: null;
    }

    /**
     * @inheritDoc
     */
    public function close(): static
    {
        if ($this->cursor) {
            pg_free_result($this->cursor);
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

        return pg_affected_rows($this->cursor);
    }

    /**
     * @inheritDoc
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        $insertQuery = $this->query;

        preg_match('/insert\s*into\s*[\"]*(\W\w+)[\"]*/i', $insertQuery, $matches);

        if (!isset($matches[1])) {
            return null;
        }

        $table = [$matches[1]];

        /* find sequence column name */
        $colNameQuery = $this->createQuery();

        $colNameQuery->select('column_default')
            ->from('information_schema.columns')
            ->whereRaw('table_name = %q', $this->driver->replacePrefix(trim($table[0], '" ')))
            ->whereRaw('column_default LIKE %q', '%nextval%');

        $stmt = pg_query($this->conn, (string) $colNameQuery);

        $colName = pg_fetch_result($stmt, 0, 0);

        $changedColName = str_replace('nextval', 'currval', $colName);

        $insertidQuery = $this->createQuery();

        $insertidQuery->selectRaw($changedColName);

        $stmt = pg_query($this->conn, (string) $insertidQuery);

        return pg_fetch_result($stmt, 0, 0);
    }

    /**
     * createQuery
     *
     * @return  Query
     */
    protected function createQuery(): Query
    {
        return new Query($this->driver, AbstractGrammar::create($this->driver->getPlatformName()));
    }
}
