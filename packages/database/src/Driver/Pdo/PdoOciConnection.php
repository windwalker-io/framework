<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use PDO;
use Throwable;
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

    protected function doConnect(DriverOptions $options): PDO
    {
        $pdo = parent::doConnect($options);

        // Make 'Y-m-d H:i:s' strings acceptable for DATE / TIMESTAMP columns.
        $pdo->exec(
            "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'"
            . " NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF'"
            . " NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF TZH:TZM'"
        );

        return $pdo;
    }

    public function ping(): bool
    {
        try {
            $this->connect();

            $this->connection->query('SELECT 1 FROM DUAL');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
