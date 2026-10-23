<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Mysqli;

use mysqli;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\DriverOptions;

/**
 * The MysqliConnection class.
 */
class MysqliConnection extends AbstractConnection
{
    protected static string $name = 'mysqli';

    /**
     * @var mysqli
     */
    protected mixed $connection = null;

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return extension_loaded('mysqli');
    }

    public static function prepareDbOptions(DriverOptions $options): DriverOptions
    {
        if ($options->tls !== null) {
            $options->extra['tls'] = array_replace(
                [
                    'key' => null,
                    'cert' => null,
                    'ca' => null,
                    'capath' => null,
                    'cipherAlgos' => null,
                ],
                $options->tls
            );
        }

        return $options;
    }

    protected function doConnect(DriverOptions $options): bool|mysqli
    {
        mysqli_report(MYSQLI_REPORT_ALL | MYSQLI_REPORT_STRICT);

        $connection = mysqli_init();

        if (!$connection) {
            return false;
        }

        if ($options->tls !== null) {
            $tls = $options->extra['tls'] ?? [];

            $connection->ssl_set(
                $tls['key'],
                $tls['cert'],
                $tls['ca'],
                $tls['capath'],
                $tls['cipherAlgos']
            );
        }

        $connection->real_connect(
            $options->host ?? null,
            $options->user ?? null,
            $options->password ?? null,
            $options->dbname ?? null,
            $options->port ?? null,
            $options->unixSocket ?? null,
        );

        return $connection;
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        if (!$this->isConnected()) {
            return true;
        }

        $r = $this->connection->close();

        $this->connection = null;

        return $r;
    }

    /**
     * @return mysqli
     */
    public function get(): ?mysqli
    {
        return parent::get();
    }

    public function ping(): bool
    {
        try {
            $this->connect();

            return $this->connection->query('SELECT 1') !== false;
        } catch (\Throwable) {
            return false;
        }
    }
}
