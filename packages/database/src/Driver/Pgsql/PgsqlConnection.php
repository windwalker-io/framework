<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pgsql;

use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\Database\Driver\Pdo\DsnHelper;
use Windwalker\Database\Exception\DatabaseConnectException;

/**
 * The PgsqlConnection class.
 */
class PgsqlConnection extends AbstractConnection
{
    protected static string $name = 'pgsql';

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return extension_loaded('pgsql');
    }

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        $params = [];

        $params['host'] = $options->host;
        $params['port'] = $options->port;
        $params['dbname'] = $options->dbname;
        $params['user'] = $options->user;
        $params['password'] = $options->password;

        if (isset($options->charset)) {
            $params['options'] = sprintf(
                "'--client_encoding=%s'",
                strtoupper($options->charset)
            );
        }

        $options->dsn = DsnHelper::build($params, null, ' ');

        return $options;
    }

    protected function doConnect(DriverOptions $options)
    {
        $res = @pg_connect($options->dsn);

        if (!$res) {
            throw new DatabaseConnectException('Unable to connect to pgsql.');
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        if (!$this->isConnected()) {
            return true;
        }

        $r = pg_close($this->connection);

        $this->connection = null;

        return $r;
    }

    public function ping(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return pg_ping($this->connection);
    }
}
