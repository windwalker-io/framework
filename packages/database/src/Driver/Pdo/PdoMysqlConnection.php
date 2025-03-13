<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

/**
 * The MysqlConnection class.
 */
class PdoMysqlConnection extends AbstractPdoConnection
{
    protected static string $dbtype = 'mysql';

    public static function getParameters(array $options): array
    {
        $params['host'] = $options['host'] ?? null;
        $params['port'] = $options['port'] ?? null;
        $params['unix_socket'] = $options['unix_socket'] ?? null;
        $params['dbname'] = $options['dbname'] ?? null;
        $params['charset'] = $options['charset'] ?? 'utf8mb4';

        $options['dsn'] ??= static::getDsn($params);

        if (strtolower($params['charset']) === 'utf8mb4') {
            $options['driverOptions'][\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4';
        } elseif (strtolower($params['charset']) === 'utf8') {
            $options['driverOptions'][\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
        }

        $options['driverOptions'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = false;

        return $options;
    }
}
