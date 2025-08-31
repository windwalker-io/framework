<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Driver\DriverOptions;

/**
 * The PdoPgsqlConnection class.
 */
class PdoPgsqlConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'pgsql';

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        $params['host'] = $options->host;
        $params['port'] = $options->port ?? null;
        $params['dbname'] = $options->dbname ?? null;
        $params['charset'] = $options->charset ?? null;

        $options->dsn ??= static::getDsn($params);

        return $options;
    }
}
