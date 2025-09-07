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
        return $options;
    }

    protected function doConnect(DriverOptions $options): bool|mysqli
    {
        mysqli_report(MYSQLI_REPORT_ALL | MYSQLI_REPORT_STRICT);

        return mysqli_connect(
            $options->host ?? null,
            $options->user ?? null,
            $options->password ?? null,
            $options->dbname ?? null,
            $options->port ?? null,
            $options->unixSocket ?? null,
        );
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
