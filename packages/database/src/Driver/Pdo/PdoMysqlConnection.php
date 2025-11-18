<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Pdo\Mysql;
use Windwalker\Database\Driver\DriverOptions;

/**
 * The MysqlConnection class.
 */
class PdoMysqlConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'mysql';

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        $params['host'] = $options->host;
        $params['port'] = $options->port;
        $params['unix_socket'] = $options->unixSocket;
        $params['dbname'] = $options->dbname;
        $params['charset'] = $options->charset ?? 'utf8mb4';

        $options->dsn ??= static::getDsn($params);

        if (version_compare(PHP_VERSION, '8.4', '>=')) {
            if (strtolower($params['charset']) === 'utf8mb4') {
                $options->driverOptions[Mysql::ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4';
            } elseif (strtolower($params['charset']) === 'utf8') {
                $options->driverOptions[Mysql::ATTR_INIT_COMMAND] = 'SET NAMES utf8';
            }
        } else {
            if (strtolower($params['charset']) === 'utf8mb4') {
                $options->driverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4';
            } elseif (strtolower($params['charset']) === 'utf8') {
                $options->driverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
            }
        }

        return $options;
    }

    public function disableBufferedQuery(bool $buffered = true): static
    {
        $pdo = $this->get();

        $pdo?->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$buffered);

        return $this;
    }
}
