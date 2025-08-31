<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\DriverOptions;

/**
 * The PdoSqlsrvConnection class.
 */
class PdoSqlsrvConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'sqlsrv';

    protected static array $defaultAttributes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,

        // @see \PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE
        1005 => true,
    ];

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        $params['Server'] = $options->host;

        if ($options->port) {
            $params['Server'] .= ',' . $options->port;
        }

        $params['Database'] = $options->dbname ;
        $params['CharacterSet'] = $options->charset;
        $params['MultipleActiveResultSets'] = 'True';

        $options->dsn ??= static::getDsn($params);

        return $options;
    }
}
