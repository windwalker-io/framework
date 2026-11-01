<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Pdo\Sqlite;
use Windwalker\Database\Driver\DriverOptions;

/**
 * The PdoSqliteConnection class.
 *
 * @method  Sqlite|null  get()
 */
class PdoSqliteConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'sqlite';

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        // If host is default, we should ignore it.
        if ($options->host === 'localhost') {
            $options->host = null;
        }

        $options->dsn ??= static::$dbtype . ':'
            . ($options->host ?? $options->file ?? $options->dbname ?? ':memory:');

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        return parent::disconnect();
    }

    protected function pdoConnect(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null
    ): Sqlite {
        return new Sqlite($dsn, $username, $password, $options);
    }
}
