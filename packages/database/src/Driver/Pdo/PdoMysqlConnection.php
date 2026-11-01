<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Pdo\Mysql;
use Windwalker\Database\Driver\DriverOptions;

/**
 * The MysqlConnection class.
 *
 * @method  Mysql|null  get()
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

        if ($options->tls !== null) {
            $options->driverOptions[Mysql::ATTR_SSL_KEY] = $options->tls['key'] ?? null;
            $options->driverOptions[Mysql::ATTR_SSL_CERT] = $options->tls['cert'] ?? null;
            $options->driverOptions[Mysql::ATTR_SSL_CA] = $options->tls['ca'] ?? null;
            $options->driverOptions[Mysql::ATTR_SSL_CAPATH] = $options->tls['capath'] ?? null;
            $options->driverOptions[Mysql::ATTR_SSL_CIPHER] = $options->tls['cipherAlgos'] ?? null;
            $options->driverOptions[Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = $options->tls['verify'] ?? true;
        }

        if (strtolower($params['charset']) === 'utf8mb4') {
            $options->driverOptions[Mysql::ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4';
        } elseif (strtolower($params['charset']) === 'utf8') {
            $options->driverOptions[Mysql::ATTR_INIT_COMMAND] = 'SET NAMES utf8';
        }

        return $options;
    }

    public function disableBufferedQuery(bool $buffered = true): static
    {
        $pdo = $this->get();

        $pdo?->setAttribute(Mysql::ATTR_USE_BUFFERED_QUERY, !$buffered);

        return $this;
    }

    protected function pdoConnect(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null,
    ): Mysql {
        return new Mysql($dsn, $username, $password, $options);
    }
}
