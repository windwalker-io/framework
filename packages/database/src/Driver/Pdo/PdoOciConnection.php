<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\DriverOptions;

/**
 * The PdoOciConnection class.
 *
 * `dbname` is the Oracle service name (e.g. FREEPDB1), not a schema.
 */
class PdoOciConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'oci';

    protected static array $defaultAttributes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_CASE => PDO::CASE_LOWER,
    ];

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        if ($options->dsn !== null) {
            return $options;
        }

        $host = $options->host ?: 'localhost';
        $port = $options->port ?: 1521;

        $dbname = '//' . $host . ':' . $port;

        if ($options->dbname) {
            $dbname .= '/' . $options->dbname;
        }

        $options->dsn = static::getDsn(
            [
                'dbname' => $dbname,
                'charset' => $options->charset ?: 'AL32UTF8',
            ]
        );

        return $options;
    }
}
